<?php
/**
 * Easily embed videos from various Video Sharing sites
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die();
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_vshare extends DokuWiki_Syntax_Plugin {
    var $sites;

    /**
     * Constructor.
     * Intitalizes the supported video sites
     */
    function syntax_plugin_vshare(){
        $this->sites =  confToHash(dirname(__FILE__).'/sites.conf');
    }

    function getType(){
        return 'substition';
    }

    function getPType(){
        return 'block';
    }

    function getSort(){
        return 159;
    }


    /**
     * Connect to the parser
     */
    function connectTo($mode) {
        $pattern = join('|',array_keys($this->sites));
        $this->Lexer->addSpecialPattern('\{\{\s?(?:'.$pattern.')>[^}]*\}\}',$mode,'plugin_vshare');
    }

    /**
     * Parse the parameters
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        $command = substr($match,2,-2);

        // title
        list($command,$title) = explode('|',$command);
        $title = trim($title);

        // alignment
        $align = 0;
        if(substr($command,0,1) == ' ') $align += 1;
        if(substr($command,-1) == ' ')  $align += 2;
        $command = trim($command);

        // get site and video
        list($site,$vid) = explode('>',$command);
        if(!$this->sites[$site]) return null; // unknown site
        if(!$vid) return null; // no video!?

        // what size?
        list($vid,$param) = explode('?',$vid,2);
        if(preg_match('/(\d+)x(\d+)/i',$param,$m)){     // custom
            $width  = $m[1];
            $height = $m[2];
        }elseif(strpos($param,'small') !== false){      // small
            $width  = 255;
            $height = 210;
        }elseif(strpos($param,'large') !== false){      // large
            $width  = 520;
            $height = 406;
        }else{                                          // medium
            $width  = 425;
            $height = 350;
        }

        $paramm = array();
        parse_str($param, $paramm);
        $urlparam = array();
        foreach($paramm as $key => $value) {
            switch($key) {
                case 'rel':
                case 'autoplay':
                case 'ap':
                    if($paramm[$key] === '1' || $paramm[$key] === '0') {
                        $urlparam[] = $key . '=' . $paramm[$key];
                    }
                    break;
                case 'start':
                case 'end':
                case 'chapter_id': //for twitch.tv
                case 'initial_time':
                case 'offsetTime':
                case 'startSlide':
                    $number = (int) $paramm[$key];
                    if($number > 0) {
                        $urlparam[] = $key . '=' . $number;
                    }
                    break;
                case 'auto_start':
                    if($paramm[$key] === 'true' || $paramm[$key] === 'false') {
                        $urlparam[] = $key . '=' . $paramm[$key];
                    }
                    break;
            }
        }

        list($type, $url) = explode(' ', $this->sites[$site], 2);
        $url  = trim($url);
        $type = trim($type);
        $url  = str_replace('@VIDEO@',rawurlencode($vid),$url);
        $url  = str_replace('@WIDTH@',$width,$url);
        $url  = str_replace('@HEIGHT@',$height,$url);
        if(count($urlparam)) {
            if(strpos($url, '?') !== false) {
                $sepchar = '&';
            } else {
                $sepchar = '?';
            }
            $url .= $sepchar . implode('&', $urlparam);
        }

        list(,$vars) = explode('?',$url,2);
        $varr = array();
        parse_str($vars,$varr);

        return array(
            'site'   => $site,
            'video'  => $vid,
            'url'    => $url,
            'vars'   => $varr,
            'align'  => $align,
            'width'  => $width,
            'height' => $height,
            'title'  => $title,
            'type'   => $type
        );
    }

    /**
     * Render the flash player
     */
    function render($mode, Doku_Renderer $R, $data){
        if($mode != 'xhtml') return false;
        if(is_null($data)) return false;

        if($data['align'] == 0) $align = 'none';
        if($data['align'] == 1) $align = 'right';
        if($data['align'] == 2) $align = 'left';
        if($data['align'] == 3) $align = 'center';
        if($data['title']) $title = ' title="'.hsc($data['title']).'"';

        if(is_a($R,'renderer_plugin_dw2pdf')){
            // Output for PDF renderer
            $R->doc .= '<div class="vshare__'.$align.'"
                             width="'.$data['width'].'"
                             height="'.$data['height'].'">';

            $R->doc .= '<a href="'.$data['url'].'" class="vshare">';
            $R->doc .= '<img src="'.DOKU_BASE.'lib/plugins/vshare/video.png" />';
            $R->doc .= '</a>';

            $R->doc .= '<br />';

            $R->doc .= '<a href="'.$data['url'].'" class="vshare">';
            $R->doc .= ($data['title'] ? hsc($data['title']) : 'Video');
            $R->doc .= '</a>';

            $R->doc .= '</div>';
        }else{
            // use redirector for HTTP embeds on SSL sites
            if(is_ssl() && substr($data['url'], 0, 7) == 'http://') {
                $data['url'] = DOKU_BASE.'lib/plugins/vshare/redir.php'.
                               '?url='.rawurlencode($data['url']).
                               '&hash='.md5(auth_cookiesalt().'vshare'.$data['url']);
            }

            // Normal output
            if($data['type'] == 'flash') {
                // embed flash
                $R->doc .= '<div class="vshare__'.$align.'"'.$title.'>';
                $R->doc .= html_flashobject(
                                    $data['url'],
                                    $data['width'],
                                    $data['height'],
                                    $data['vars'],
                                    $data['vars']);
                $R->doc .= '</div>';
            }else{
                // embed iframe
                $R->doc .= '<iframe ';
                $R->doc .= buildAttributes(array(
                            'src' => $data['url'],
                            'height' => $data['height'],
                            'width'  => $data['width'],
                            'class'  => 'vshare__'.$align,
                            'allowfullscreen' => '',
                            'frameborder' => 0,
                            'scrolling' => 'no'
                           ));
                $R->doc .= '>'.hsc($data['title']).'</iframe>';
            }
        }
    }
}
