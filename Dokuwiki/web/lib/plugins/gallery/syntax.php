<?php
/**
 * Embed an image gallery
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Joe Lapp <joe.lapp@pobox.com>
 * @author     Dave Doyle <davedoyle.canadalawbook.ca>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');

class syntax_plugin_gallery extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 301;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{gallery>[^}]*\}\}',$mode,'plugin_gallery');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        global $ID;
        $match = substr($match,10,-2); //strip markup from start and end

        $data = array();

        $data['galid'] = substr(md5($match),0,4);

        // alignment
        $data['align'] = 0;
        if(substr($match,0,1) == ' ') $data['align'] += 1;
        if(substr($match,-1,1) == ' ') $data['align'] += 2;

        // extract params
        list($ns,$params) = explode('?',$match,2);
        $ns = trim($ns);

        // namespace (including resolving relatives)
        if(!preg_match('/^https?:\/\//i',$ns)){
            $data['ns'] = resolve_id(getNS($ID),$ns);
        }else{
            $data['ns'] =  $ns;
        }

        // set the defaults
        $data['tw']       = $this->getConf('thumbnail_width');
        $data['th']       = $this->getConf('thumbnail_height');
        $data['iw']       = $this->getConf('image_width');
        $data['ih']       = $this->getConf('image_height');
        $data['cols']     = $this->getConf('cols');
        $data['filter']   = '';
        $data['lightbox'] = false;
        $data['direct']   = false;
        $data['showname'] = false;
        $data['showtitle'] = false;
        $data['reverse']  = false;
        $data['random']   = false;
        $data['cache']    = true;
        $data['crop']     = false;
        $data['recursive']= true;
        $data['sort']     = $this->getConf('sort');
        $data['limit']    = 0;
        $data['offset']   = 0;
        $data['paginate'] = 0;

        // parse additional options
        $params = $this->getConf('options').','.$params;
        $params = preg_replace('/[,&\?]+/',' ',$params);
        $params = explode(' ',$params);
        foreach($params as $param){
            if($param === '') continue;
            if($param == 'titlesort'){
                $data['sort'] = 'title';
            }elseif($param == 'datesort'){
                $data['sort'] = 'date';
            }elseif($param == 'modsort'){
                $data['sort'] = 'mod';
            }elseif(preg_match('/^=(\d+)$/',$param,$match)){
                $data['limit'] = $match[1];
            }elseif(preg_match('/^\+(\d+)$/',$param,$match)){
                $data['offset'] = $match[1];
            }elseif(is_numeric($param)){
                $data['cols'] = (int) $param;
            }elseif(preg_match('/^~(\d+)$/',$param,$match)){
                $data['paginate'] = $match[1];
            }elseif(preg_match('/^(\d+)([xX])(\d+)$/',$param,$match)){
                if($match[2] == 'X'){
                    $data['iw'] = $match[1];
                    $data['ih'] = $match[3];
                }else{
                    $data['tw'] = $match[1];
                    $data['th'] = $match[3];
                }
            }elseif(strpos($param,'*') !== false){
                $param = preg_quote($param,'/');
                $param = '/^'.str_replace('\\*','.*?',$param).'$/';
                $data['filter'] = $param;
            }else{
                if(substr($param,0,2) == 'no'){
                    $data[substr($param,2)] = false;
                }else{
                    $data[$param] = true;
                }
            }
        }

        // implicit direct linking?
        if($data['lightbox']) $data['direct']   = true;


        return $data;
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $R, $data){
        global $ID;
        if($mode == 'xhtml'){
            $R->info['cache'] &= $data['cache'];
            $R->doc .= $this->_gallery($data);
            return true;
        }elseif($mode == 'metadata'){
            $rel = p_get_metadata($ID,'relation',METADATA_RENDER_USING_CACHE);
            $img = $rel['firstimage'];
            if(empty($img)){
                $files = $this->_findimages($data);
                if(count($files)) $R->internalmedia($files[0]['id']);
            }
            return true;
        }
        return false;
    }

    /**
     * Loads images from a MediaRSS or ATOM feed
     */
    function _loadRSS($url){
        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->set_feed_url($url);
        $feed->init();
        $files = array();

        // base url to use for broken feeds with non-absolute links
        $main = parse_url($url);
        $host = $main['scheme'].'://'.
                $main['host'].
                (($main['port'])?':'.$main['port']:'');
        $path = dirname($main['path']).'/';

        foreach($feed->get_items() as $item){
            if ($enclosure = $item->get_enclosure()){
                // skip non-image enclosures
                if($enclosure->get_type()){
                    if(substr($enclosure->get_type(),0,5) != 'image') continue;
                }else{
                    if(!preg_match('/\.(jpe?g|png|gif)(\?|$)/i',
                       $enclosure->get_link())) continue;
                }

                // non absolute links
                $ilink = $enclosure->get_link();
                if(!preg_match('/^https?:\/\//i',$ilink)){
                    if($ilink{0} == '/'){
                        $ilink = $host.$ilink;
                    }else{
                        $ilink = $host.$path.$ilink;
                    }
                }
                $link = $item->link;
                if(!preg_match('/^https?:\/\//i',$link)){
                    if($link{0} == '/'){
                        $link = $host.$link;
                    }else{
                        $link = $host.$path.$link;
                    }
                }

                $files[] = array(
                    'id'     => $ilink,
                    'isimg'  => true,
                    'file'   => basename($ilink),
                    // decode to avoid later double encoding
                    'title'  => htmlspecialchars_decode($enclosure->get_title(),ENT_COMPAT),
                    'desc'   => strip_tags(htmlspecialchars_decode($enclosure->get_description(),ENT_COMPAT)),
                    'width'  => $enclosure->get_width(),
                    'height' => $enclosure->get_height(),
                    'mtime'  => $item->get_date('U'),
                    'ctime'  => $item->get_date('U'),
                    'detail' => $link,
                );
            }
        }
        return $files;
    }

    /**
     * Gather all photos matching the given criteria
     */
    function _findimages(&$data){
        global $conf;
        $files = array();

        // http URLs are supposed to be media RSS feeds
        if(preg_match('/^https?:\/\//i',$data['ns'])){
            $files = $this->_loadRSS($data['ns']);
            $data['_single'] = false;
        }else{
            $dir = utf8_encodeFN(str_replace(':','/',$data['ns']));
            // all possible images for the given namespace (or a single image)
            if(is_file($conf['mediadir'].'/'.$dir)){
                require_once(DOKU_INC.'inc/JpegMeta.php');
                $files[] = array(
                    'id'    => $data['ns'],
                    'isimg' => preg_match('/\.(jpe?g|gif|png)$/',$dir),
                    'file'  => basename($dir),
                    'mtime' => filemtime($conf['mediadir'].'/'.$dir),
                    'meta'  => new JpegMeta($conf['mediadir'].'/'.$dir)
                );
                $data['_single'] = true;
            }else{
                $depth = $data['recursive'] ? 0 : 1;
                search($files,
                       $conf['mediadir'],
                       'search_media',
                       array('depth'=>$depth),
                       $dir);
                $data['_single'] = false;
            }
        }

        // done, yet?
        $len = count($files);
        if(!$len) return $files;
        if($data['single']) return $files;

        // filter images
        for($i=0; $i<$len; $i++){
            if(!$files[$i]['isimg']){
                unset($files[$i]); // this is faster, because RE was done before
            }elseif($data['filter']){
                if(!preg_match($data['filter'],noNS($files[$i]['id']))) unset($files[$i]);
            }
        }
        if($len<1) return $files;

        // random?
        if($data['random']){
            shuffle($files);
        }else{
            // sort?
            if($data['sort'] == 'date'){
                usort($files,array($this,'_datesort'));
            }elseif($data['sort'] == 'mod'){
                usort($files,array($this,'_modsort'));
            }elseif($data['sort'] == 'title'){
                usort($files,array($this,'_titlesort'));
            }

            // reverse?
            if($data['reverse']) $files = array_reverse($files);
        }

        // limits and offsets?
        if($data['offset']) $files = array_slice($files,$data['offset']);
        if($data['limit']) $files = array_slice($files,0,$data['limit']);

        return $files;
    }

    /**
     * usort callback to sort by file lastmodified time
     */
    function _modsort($a,$b){
        if($a['mtime'] < $b['mtime']) return -1;
        if($a['mtime'] > $b['mtime']) return 1;
        return strcmp($a['file'],$b['file']);
    }

    /**
     * usort callback to sort by EXIF date
     */
    function _datesort($a,$b){
        $da = $this->_meta($a,'cdate');
        $db = $this->_meta($b,'cdate');
        if($da < $db) return -1;
        if($da > $db) return 1;
        return strcmp($a['file'],$b['file']);
    }

    /**
     * usort callback to sort by EXIF title
     */
    function _titlesort($a,$b){
        $ta = $this->_meta($a,'title');
        $tb = $this->_meta($b,'title');
        return strcmp($ta,$tb);
    }


    /**
     * Does the gallery formatting
     */
    function _gallery($data){
        global $conf;
        global $lang;
        $ret = '';

        $files = $this->_findimages($data);

        //anything found?
        if(!count($files)){
            $ret .= '<div class="nothing">'.$lang['nothingfound'].'</div>';
            return $ret;
        }

        // prepare alignment
        $align = '';
        $xalign = '';
        if($data['align'] == 1){
            $align  = ' gallery_right';
            $xalign = ' align="right"';
        }
        if($data['align'] == 2){
            $align  = ' gallery_left';
            $xalign = ' align="left"';
        }
        if($data['align'] == 3){
            $align  = ' gallery_center';
            $xalign = ' align="center"';
        }
        if(!$data['_single']){
            if(!$align) $align = ' gallery_center'; // center galleries on default
            if(!$xalign) $xalign = ' align="center"';
        }

        $page = 0;

        // build gallery
        if($data['_single']){
            $ret .= $this->_image($files[0],$data);
            $ret .= $this->_showname($files[0],$data);
            $ret .= $this->_showtitle($files[0],$data);
        }elseif($data['cols'] > 0){ // format as table
            $close_pg = false;

            $i = 0;
            foreach($files as $img){

                // new page?
                if($data['paginate'] && ($i % $data['paginate'] == 0)){
                     $ret .= '<div class="gallery_page gallery__'.$data['galid'].'" id="gallery__'.$data['galid'].'_'.(++$page).'">';
                     $close_pg = true;
                }

                // new table?
                if($i == 0 || ($data['paginate'] && ($i % $data['paginate'] == 0))){
                    $ret .= '<table>';

                }

                // new row?
                if($i % $data['cols'] == 0){
                    $ret .= '<tr>';
                }

                // an image cell
                $ret .= '<td>';
                $ret .= $this->_image($img,$data);
                $ret .= $this->_showname($img,$data);
                $ret .= $this->_showtitle($img,$data);
                $ret .= '</td>';
                $i++;

                // done with this row? cloase it
                $close_tr = true;
                if($i % $data['cols'] == 0){
                    $ret .= '</tr>';
                    $close_tr = false;
                }

                // close current page and table
                if($data['paginate'] && ($i % $data['paginate'] == 0)){
                    if ($close_tr){
                        // add remaining empty cells
                        while($i % $data['cols']){
                            $ret .= '<td></td>';
                            $i++;
                        }
                        $ret .= '</tr>';
                    }
                    $ret .= '</table>';
                    $ret .= '</div>';
                    $close_pg = false;
                }

            }

            if ($close_tr){
                // add remaining empty cells
                while($i % $data['cols']){
                    $ret .= '<td></td>';
                    $i++;
                }
                $ret .= '</tr>';
            }

            if(!$data['paginate']){
                $ret .= '</table>';
            }elseif ($close_pg){
                $ret .= '</table>';
                $ret .= '</div>';
            }
        }else{ // format as div sequence
            $i = 0;
            $close_pg = false;
            foreach($files as $img){

                if($data['paginate'] && ($i % $data['paginate'] == 0)){
                     $ret .= '<div class="gallery_page gallery__'.$data['galid'].'" id="gallery__'.$data['galid'].'_'.(++$page).'">';
                     $close_pg = true;
                }

                $ret .= '<div>';
                $ret .= $this->_image($img,$data);
                $ret .= $this->_showname($img,$data);
                $ret .= $this->_showtitle($img,$data);
                $ret .= '</div> ';

                $i++;

                if($data['paginate'] && ($i % $data['paginate'] == 0)){
                    $ret .= '</div>';
                    $close_pg = false;
                }
            }

            if($close_pg) $ret .= '</div>';

            $ret .= '<br style="clear:both" />';
        }

        // pagination links
        $pgret = '';
        if($page){
            $pgret .= '<div class="gallery_pages"><span>'.$this->getLang('pages').' </span>';
            for($j=1; $j<=$page; $j++){
                $pgret .= '<a href="#gallery__'.$data['galid'].'_'.$j.'" class="gallery_pgsel button">'.$j.'</a> ';
            }
            $pgret .= '</div>';
        }

        return '<div class="gallery'.$align.'"'.$xalign.'>'.$pgret.$ret.'<div class="clearer"></div></div>';
    }

    /**
     * Defines how a thumbnail should look like
     */
    function _image(&$img,$data){
        global $ID;

        // calculate thumbnail size
        if(!$data['crop']){
            $w = (int) $this->_meta($img,'width');
            $h = (int) $this->_meta($img,'height');
            if($w && $h){
                $dim = array();
                if($w > $data['tw'] || $h > $data['th']){
                    $ratio = $this->_ratio($img,$data['tw'],$data['th']);
                    $w = floor($w * $ratio);
                    $h = floor($h * $ratio);
                    $dim = array('w'=>$w,'h'=>$h);
                }
            }else{
                $data['crop'] = true; // no size info -> always crop
            }
        }
        if($data['crop']){
            $w = $data['tw'];
            $h = $data['th'];
            $dim = array('w'=>$w,'h'=>$h);
        }

        //prepare img attributes
        $i             = array();
        $i['width']    = $w;
        $i['height']   = $h;
        $i['border']   = 0;
        $i['alt']      = $this->_meta($img,'title');
        $i['class']    = 'tn';
        $iatt = buildAttributes($i);
        $src  = ml($img['id'],$dim);

        // prepare lightbox dimensions
        $w_lightbox = (int) $this->_meta($img,'width');
        $h_lightbox = (int) $this->_meta($img,'height');
        $dim_lightbox = array();
        if($w_lightbox > $data['iw'] || $h_lightbox > $data['ih']){
            $ratio = $this->_ratio($img,$data['iw'],$data['ih']);
            $w_lightbox = floor($w_lightbox * $ratio);
            $h_lightbox = floor($h_lightbox * $ratio);
            $dim_lightbox = array('w'=>$w_lightbox,'h'=>$h_lightbox);
        }

        //prepare link attributes
        $a           = array();
        $a['title']  = $this->_meta($img,'title');
        $a['data-caption'] = trim(str_replace("\n",' ',$this->_meta($img,'desc')));
        if(!$a['data-caption']) unset($a['data-caption']);
        if($data['lightbox']){
            $href   = ml($img['id'],$dim_lightbox);
            $a['class'] = "lightbox JSnocheck";
            $a['rel']   = 'lightbox[gal-'.substr(md5($ID),4).']'; //unique ID for the gallery
        }elseif($img['detail'] && !$data['direct']){
            $href   = $img['detail'];
        }else{
            $href   = ml($img['id'],array('id'=>$ID),$data['direct']);
        }
        $aatt = buildAttributes($a);

        // prepare output
        $ret  = '';
        $ret .= '<a href="'.$href.'" '.$aatt.'>';
        $ret .= '<img src="'.$src.'" '.$iatt.' />';
        $ret .= '</a>';
        return $ret;
    }


    /**
     * Defines how a filename + link should look
     */
    function _showname($img,$data){
        global $ID;

        if(!$data['showname'] ) { return ''; }

        //prepare link
        $lnk = ml($img['id'],array('id'=>$ID),false);

        // prepare output
        $ret  = '';
        $ret .= '<br /><a href="'.$lnk.'">';
        $ret .= hsc($img['file']);
        $ret .= '</a>';
        return $ret;
    }

    /**
     * Defines how title + link should look
     */
    function _showtitle($img,$data){
        global $ID;

        if(!$data['showtitle'] ) { return ''; }

        //prepare link
        $lnk = ml($img['id'],array('id'=>$ID),false);

        // prepare output
        $ret  = '';
        $ret .= '<br /><a href="'.$lnk.'">';
        $ret .= hsc($this->_meta($img,'title'));
        $ret .= '</a>';
        return $ret;
    }

    /**
     * Return the metadata of an item
     *
     * Automatically checks if a JPEGMeta object is available or if all data is
     * supplied in array
     */
    function _meta(&$img,$opt){
        if($img['meta']){
            // map JPEGMeta calls to opt names

            switch($opt){
                case 'title':
                    return $img['meta']->getField('Simple.Title');
                case 'desc':
                    return $img['meta']->getField('Iptc.Caption');
                case 'cdate':
                    return $img['meta']->getField('Date.EarliestTime');
                case 'width':
                    return $img['meta']->getField('File.Width');
                case 'height':
                    return $img['meta']->getField('File.Height');


                default:
                    return '';
            }

        }else{
            // just return the array field
            return $img[$opt];
        }
    }

    /**
     * Calculates the multiplier needed to resize the image to the given
     * dimensions
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _ratio(&$img,$maxwidth,$maxheight=0){
        if(!$maxheight) $maxheight = $maxwidth;

        $w = $this->_meta($img,'width');
        $h = $this->_meta($img,'height');

        $ratio = 1;
        if($w >= $h){
            if($w >= $maxwidth){
                $ratio = $maxwidth/$w;
            }elseif($h > $maxheight){
                $ratio = $maxheight/$h;
            }
        }else{
            if($h >= $maxheight){
                $ratio = $maxheight/$h;
            }elseif($w > $maxwidth){
                $ratio = $maxwidth/$w;
            }
        }
        return $ratio;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
