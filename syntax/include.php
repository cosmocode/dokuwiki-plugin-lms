<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * DokuWiki Plugin lms (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class syntax_plugin_lms_include extends SyntaxPlugin
{
    /** @var helper_plugin_lms */
    protected $hlp;

    /** @var string current user */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $INPUT;
        $this->hlp = $this->loadHelper('lms');
        $this->user = $INPUT->server->str('REMOTE_USER');
    }

    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 150;
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~LMSINCLUDE~~', $mode, 'plugin_lms_include');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = [];

        return $data;
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }
        $renderer->nocache();
        if (!$this->user) return true;

        global $INFO;
        $seen = $this->hlp->getLesson($INFO['id'], $this->user);
        if ($seen === false) return true; // we're not on a lesson page

        $cp = $this->hlp->getControlPage();
        if (!$cp) return true;

        $renderer->doc .= tpl_include_page($cp, false);

        return true;
    }
}
