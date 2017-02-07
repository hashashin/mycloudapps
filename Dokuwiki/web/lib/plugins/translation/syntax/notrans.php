<?php
/**
 * Translation Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_translation_notrans extends DokuWiki_Syntax_Plugin {

    /**
     * for th helper plugin
     */
    var $hlp = null;

    /**
     * Constructor. Load helper plugin
     */
    function __construct(){
        $this->hlp =& plugin_load('helper', 'translation');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 155;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOTRANS~~',$mode,'plugin_translation_notrans');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        return array('notrans');
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        // store info in metadata
        if($format == 'metadata'){
            $renderer->meta['plugin']['translation']['notrans'] = true;
        }
        return false;
    }

    // for backward compatibility
    function _showTranslations(){
        return $this->hlp->showTranslations();
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
