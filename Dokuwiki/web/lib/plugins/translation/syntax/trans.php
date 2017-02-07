<?php
/**
 * Translation Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_translation_trans extends DokuWiki_Syntax_Plugin {
    /**
     * What kind of syntax are we?
     */
    function getType() {
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort() {
        return 155;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~TRANS~~', $mode, 'plugin_translation_trans');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array();
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        if($format != 'xhtml') return false;

        // disable caching
        $renderer->nocache();

        /** @var helper_plugin_translation $hlp */
        $hlp =  plugin_load('helper', 'translation');
        $renderer->doc .= $hlp->showTranslations();
        return true;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
