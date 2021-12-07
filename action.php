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
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleAction');

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

        $JSINFO['plugins']['lms']['seen'] = array_keys($hlp->getUserLessons($user));
    }


    /**
     * Run user actions
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleAction(Doku_Event $event, $param)
    {
        global $INPUT;
        global $ID;

        $user = $INPUT->server->str('REMOTE_USER');
        if (!$user) return;

        $act = act_clean($event->data);
        if ($act !== 'lms') return;

        $event->data = 'redirect';

        $action = $INPUT->str('lms');
        if (!$action) return;

        if (!checkSecurityToken()) return;

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');
        switch ($action) {
            case 'seen':
                $hlp->markLesson($ID, $user, true);
                break;
            case 'check':
                $hlp->markLesson($ID, $user, true);
                $next = $hlp->getNextLesson($ID, $user);
                if ($next) {
                    $ID = $next;
                }
                break;
            case 'unseen':
                $hlp->markLesson($ID, $user, false);
                break;
        }
    }

}

