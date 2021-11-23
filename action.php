<?php

/**
 * DokuWiki Plugin lms (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class action_plugin_lms extends \dokuwiki\Extension\ActionPlugin
{

    /** @inheritDoc */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'handleStart');

    }

    /**
     * Initiatilize seen info for current user
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleStart(Doku_Event $event, $param)
    {
        global $JSINFO;
        global $INPUT;

        $user = $INPUT->server->str('REMOTE_USER');
        if (!$user) return;

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');

        $JSINFO['plugins']['lms']['seen'] = array_values($hlp->getUserLessons($user));
    }

}

