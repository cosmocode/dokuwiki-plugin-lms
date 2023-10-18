<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * DokuWiki Plugin lms (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class syntax_plugin_lms_lms extends SyntaxPlugin
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
        $this->Lexer->addSpecialPattern('~~LMS~~', $mode, 'plugin_lms_lms');
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

        $renderer->doc .= '<div class="lms-nav">';
        $renderer->doc .= $this->prevButton();
        $renderer->doc .= $this->toggleButton($seen);
        $renderer->doc .= $this->nextButton();
        $renderer->doc .= '</div>';

        return true;
    }

    /**
     * Build a navigation link based on the given command
     *
     * @param string $cmd
     * @return string
     */
    protected function makeLink($id, $cmd)
    {
        $args = [
            'do' => 'lms',
            'lms' => $cmd,
            'sectok' => getSecurityToken(),
        ];

        $attr = [
            'href' => wl($id, $args, false, '&'),
            'class' => "lms-btn lms-btn-$cmd",
            'title' => $this->getLang($cmd),
        ];

        $svg = inlineSVG(__DIR__ . '/../img/' . $cmd . '.svg');
        $span = '<span class="a11y">' . hsc($this->getLang($cmd)) . '</span>';

        return '<a ' . buildAttributes($attr) . '>' . $span . $svg . '</a>';
    }

    /**
     * Toggle seen status
     *
     * @param bool|null $seen current seen status
     * @return string
     */
    public function toggleButton($seen = null)
    {
        global $INFO;

        if ($seen === null) {
            $seen = $this->hlp->getLesson($INFO['id'], $this->user);
        }

        if ($seen) {
            return $this->makeLink($INFO['id'], 'unseen');
        }
        return $this->makeLink($INFO['id'], 'seen');
    }

    /**
     * Navigate to next lesson
     *
     * @return string
     */
    public function nextButton()
    {
        global $INFO;
        $next = $this->hlp->getNextLesson($INFO['id']);
        if (!$next) return '';
        return $this->makeLink($next, 'next');
    }

    /**
     * Navigate to previous lesson
     *
     * @return string
     */
    public function prevButton()
    {
        global $INFO;
        $prev = $this->hlp->getPrevLesson($INFO['id']);
        if (!$prev) return '';
        return $this->makeLink($prev, 'prev');
    }
}
