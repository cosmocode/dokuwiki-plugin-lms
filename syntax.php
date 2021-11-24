<?php

/**
 * DokuWiki Plugin lms (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class syntax_plugin_lms extends \dokuwiki\Extension\SyntaxPlugin
{
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
        $this->Lexer->addSpecialPattern('~~LMS~~', $mode, 'plugin_lms');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();

        return $data;
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }
        $renderer->nocache();

        global $INPUT;
        $user = $INPUT->server->str('REMOTE_USER');
        if (!$user) return true;

        global $INFO;
        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');
        $seen = $hlp->getLesson($INFO['id'], $user);
        if ($seen === false) return true; // we're not on a lesson page

        $renderer->doc .= '<div class="lms-nav">';
        $renderer->doc .= $this->prevButton();
        $renderer->doc .= $this->toggleButton((bool)$seen);
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

        $svg = inlineSVG(__DIR__ . '/img/' . $cmd . '.svg');

        return '<a ' . buildAttributes($attr) . '>' . $svg . '</a>';
    }

    /**
     * Toggle seen status
     *
     * @param bool $seen
     * @return string
     */
    public function toggleButton($seen)
    {
        global $INFO;

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

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');
        $next = $hlp->getNextLesson($INFO['id']);

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

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');
        $prev = $hlp->getPrevLesson($INFO['id']);

        if (!$prev) return '';
        return $this->makeLink($prev, 'prev');
    }
}
