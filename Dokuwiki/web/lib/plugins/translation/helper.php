<?php
/**
 * Translation Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_translation extends DokuWiki_Plugin {
    var $translations = array();
    var $translationNs = '';
    var $defaultlang = '';
    var $LN = array(); // hold native names
    var $opts = array(); // display options

    /**
     * Initialize
     */
    function __construct() {
        global $conf;
        require_once(DOKU_INC . 'inc/pageutils.php');
        require_once(DOKU_INC . 'inc/utf8.php');

        // load wanted translation into array
        $this->translations = strtolower(str_replace(',', ' ', $this->getConf('translations')));
        $this->translations = array_unique(array_filter(explode(' ', $this->translations)));
        sort($this->translations);

        // load language names
        $this->LN = confToHash(dirname(__FILE__) . '/lang/langnames.txt');

        // display options
        $this->opts = $this->getConf('display');
        $this->opts = explode(',', $this->opts);
        $this->opts = array_map('trim', $this->opts);
        $this->opts = array_fill_keys($this->opts, true);

        // get default translation
        if(!$conf['lang_before_translation']) {
            $dfl = $conf['lang'];
        } else {
            $dfl = $conf['lang_before_translation'];
        }
        if(in_array($dfl, $this->translations)) {
            $this->defaultlang = $dfl;
        } else {
            $this->defaultlang = '';
            array_unshift($this->translations, '');
        }

        $this->translationNs = cleanID($this->getConf('translationns'));
        if($this->translationNs) $this->translationNs .= ':';
    }

    /**
     * Check if the given ID is a translation and return the language code.
     */
    function getLangPart($id) {
        list($lng) = $this->getTransParts($id);
        return $lng;
    }

    /**
     * Check if the given ID is a translation and return the language code and
     * the id part.
     */
    function getTransParts($id) {
        $rx = '/^' . $this->translationNs . '(' . join('|', $this->translations) . '):(.*)/';
        if(preg_match($rx, $id, $match)) {
            return array($match[1], $match[2]);
        }
        return array('', $id);
    }

    /**
     * Returns the browser language if it matches with one of the configured
     * languages
     */
    function getBrowserLang() {
        $rx = '/(^|,|:|;|-)(' . join('|', $this->translations) . ')($|,|:|;|-)/i';
        if(preg_match($rx, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $match)) {
            return strtolower($match[2]);
        }
        return false;
    }

    /**
     * Returns the ID and name to the wanted translation, empty
     * $lng is default lang
     */
    function buildTransID($lng, $idpart) {
        global $conf;
        if($lng) {
            $link = ':' . $this->translationNs . $lng . ':' . $idpart;
            $name = $lng;
        } else {
            $link = ':' . $this->translationNs . $idpart;
            $name = $this->realLC('');
        }
        return array($link, $name);
    }

    /**
     * Returns the real language code, even when an empty one is given
     * (eg. resolves th default language)
     */
    function realLC($lc) {
        global $conf;
        if($lc) {
            return $lc;
        } elseif(!$conf['lang_before_translation']) {
            return $conf['lang'];
        } else {
            return $conf['lang_before_translation'];
        }
    }

    /**
     * Check if current ID should be translated and any GUI
     * should be shown
     */
    function istranslatable($id, $checkact = true) {
        global $ACT;

        if($checkact && $ACT != 'show') return false;
        if($this->translationNs && strpos($id, $this->translationNs) !== 0) return false;
        $skiptrans = trim($this->getConf('skiptrans'));
        if($skiptrans && preg_match('/' . $skiptrans . '/ui', ':' . $id)) return false;
        $meta = p_get_metadata($id);
        if(!empty($meta['plugin']['translation']['notrans'])) return false;

        return true;
    }

    /**
     * Return the (localized) about link
     */
    function showAbout() {
        global $ID;
        global $conf;
        global $INFO;

        $curlc = $this->getLangPart($ID);

        $about = $this->getConf('about');
        if($this->getConf('localabout')) {
            list($lc, $idpart) = $this->getTransParts($about);
            list($about, $name) = $this->buildTransID($curlc, $idpart);
            $about = cleanID($about);
        }

        $out = '';
        $out .= '<sup>';
        $out .= html_wikilink($about, '?');
        $out .= '</sup>';

        return $out;
    }

    /**
     * Returns a list of (lc => link) for all existing translations of a page
     *
     * @param $id
     * @return array
     */
    function getAvailableTranslations($id) {
        $result = array();

        list($lc, $idpart) = $this->getTransParts($id);
        $lang = $this->realLC($lc);

        foreach($this->translations as $t) {
            if($t == $lc) continue; //skip self
            list($link, $name) = $this->buildTransID($t, $idpart);
            if(page_exists($link)) {
                $result[$name] = $link;
            }
        }

        return $result;
    }

    /**
     * Creates an UI for linking to the available and configured translations
     *
     * Can be called from the template or via the ~~TRANS~~ syntax component.
     */
    public function showTranslations() {
        global $conf;
        global $INFO;

        if(!$this->istranslatable($INFO['id'])) return '';
        $this->checkage();

        list($lc, $idpart) = $this->getTransParts($INFO['id']);
        $lang = $this->realLC($lc);

        $out = '<div class="plugin_translation">';

        //show title and about
        if(isset($this->opts['title'])) {
            $out .= '<span>' . $this->getLang('translations');
            if($this->getConf('about')) $out .= $this->showAbout();
            $out .= ':</span> ';
            if(isset($this->opts['twolines'])) $out .= '<br />';
        }

        // open wrapper
        if($this->getConf('dropdown')) {
            // select needs its own styling
            if($INFO['exists']) {
                $class = 'wikilink1';
            } else {
                $class = 'wikilink2';
            }
            if(isset($this->opts['flag'])) {
                $flag = DOKU_BASE . 'lib/plugins/translation/flags/' . hsc($lang) . '.gif';
            }else{
                $flag = '';
            }

            if($conf['userewrite']) {
                $action = wl();
            } else {
                $action = script();
            }

            $out .= '<form action="' . $action . '" id="translation__dropdown">';
            if($flag) $out .= '<img src="' . $flag . '" alt="' . hsc($lang) . '" height="11" class="' . $class . '" /> ';
            $out .= '<select name="id" class="' . $class . '">';
        } else {
            $out .= '<ul>';
        }

        // insert items
        foreach($this->translations as $t) {
            $out .= $this->getTransItem($t, $idpart);
        }

        // close wrapper
        if($this->getConf('dropdown')) {
            $out .= '</select>';
            $out .= '<input name="go" type="submit" value="&rarr;" />';
            $out .= '</form>';
        } else {
            $out .= '</ul>';
        }

        // show about if not already shown
        if(!isset($this->opts['title']) && $this->getConf('about')) {
            $out .= '&nbsp';
            $out .= $this->showAbout();
        }

        $out .= '</div>';

        return $out;
    }

    /**
     * Return the local name
     *
     * @param $lang
     * @return string
     */
    function getLocalName($lang) {
        if($this->LN[$lang]) {
            return $this->LN[$lang];
        }
        return $lang;
    }

    /**
     * Create the link or option for a single translation
     *
     * @param $lc string      The language code
     * @param $idpart string  The ID of the translated page
     * @returns string        The item
     */
    function getTransItem($lc, $idpart) {
        global $ID;
        global $conf;

        list($link, $lang) = $this->buildTransID($lc, $idpart);
        $link = cleanID($link);

        // class
        if(page_exists($link, '', false)) {
            $class = 'wikilink1';
        } else {
            $class = 'wikilink2';
        }

        // local language name
        $localname = $this->getLocalName($lang);

        $divClass = 'li';
        // current?
        if($ID == $link) {
            $sel = ' selected="selected"';
            $class .= ' cur';
            $divClass .= ' cur';
        } else {
            $sel = '';
        }

        // flag
        if(isset($this->opts['flag'])) {
            $flag = DOKU_BASE . 'lib/plugins/translation/flags/' . hsc($lang) . '.gif';
            $style = ' style="background-image: url(\'' . $flag . '\')"';
            $class .= ' flag';
        }

        // what to display as name
        if(isset($this->opts['name'])) {
            $display = hsc($localname);
            if(isset($this->opts['langcode'])) $display .= ' (' . hsc($lang) . ')';
        } elseif(isset($this->opts['langcode'])) {
            $display = hsc($lang);
        } else {
            $display = '&nbsp;';
        }

        // prepare output
        $out = '';
        if($this->getConf('dropdown')) {
            if($conf['useslash']) $link = str_replace(':', '/', $link);

            $out .= '<option class="' . $class . '" title="' . hsc($localname) . '" value="' . $link . '"' . $sel . $style . '>';
            $out .= $display;
            $out .= '</option>';
        } else {
            $out .= "<li><div class='$divClass'>";
            $out .= '<a href="' . wl($link) . '" class="' . $class . '" title="' . hsc($localname) . '">';
            if($flag) $out .= '<img src="' . $flag . '" alt="' . hsc($lang) . '" height="11" />';
            $out .= $display;
            $out .= '</a>';
            $out .= '</div></li>';
        }

        return $out;
    }

    /**
     * Checks if the current page is a translation of a page
     * in the default language. Displays a notice when it is
     * older than the original page. Tries to link to a diff
     * with changes on the original since the translation
     */
    function checkage() {
        global $ID;
        global $INFO;
        if(!$this->getConf('checkage')) return;
        if(!$INFO['exists']) return;
        $lng = $this->getLangPart($ID);
        if($lng == $this->defaultlang) return;

        $rx = '/^' . $this->translationNs . '((' . join('|', $this->translations) . '):)?/';
        $idpart = preg_replace($rx, '', $ID);

        // compare modification times
        list($orig, $name) = $this->buildTransID($this->defaultlang, $idpart);
        $origfn = wikiFN($orig);
        if($INFO['lastmod'] >= @filemtime($origfn)) return;

        // get revision from before translation
        $orev = 0;
        $changelog = new PageChangelog($orig);
        $revs = $changelog->getRevisions(0, 100);
        foreach($revs as $rev) {
            if($rev < $INFO['lastmod']) {
                $orev = $rev;
                break;
            }
        }

        // see if the found revision still exists
        if($orev && !page_exists($orig, $orev)) $orev = 0;

        // build the message and display it
        $orig = cleanID($orig);
        $msg = sprintf($this->getLang('outdated'), wl($orig));

        $difflink = $this->getOldDiffLink($orig, $INFO['lastmod']);
        if ($difflink) {
            $msg .= sprintf(' ' . $this->getLang('diff'), $difflink);
        }

        echo '<div class="notify">' . $msg . '</div>';
    }

    function getOldDiffLink($id, $lastmod) {
        // get revision from before translation
        $orev = false;
        $changelog = new PageChangelog($id);
        $revs = $changelog->getRevisions(0, 100);
        foreach($revs as $rev) {
            if($rev < $lastmod) {
                $orev = $rev;
                break;
            }
        }
        if($orev && !page_exists($id, $orev)) {
            return false;
        }
        $id = cleanID($id);
        return wl($id, array('do' => 'diff', 'rev' => $orev));

    }
}
