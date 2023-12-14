<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin lms (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class action_plugin_lms extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'handleStart');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleAction');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAdminAjax');
    }

    /**
     * Initiatilize seen info for current user
     *
     * @param Event $event event object by reference
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleStart(Event $event, $param)
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
     * @param Event $event event object by reference
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleAction(Event $event, $param)
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

    /**
     * Check username input against all users with saved LMS data
     * and return a list of matching names
     *
     * @param Event $event
     * @return void
     */
    public function handleAdminAjax(Event $event)
    {
        if ($event->data !== 'plugin_lms_autocomplete') return;
        global $INPUT;

        if (!checkSecurityToken()) return;

        $event->preventDefault();
        $event->stopPropagation();

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');

        $knownUsers = $hlp->getKnownUsers();

        $search = $INPUT->str('user');
        $found = array_filter($knownUsers, function ($user) use ($search) {
            return (strstr(strtolower($user), strtolower($search))) !== false ? $user : null;
        });

        header('Content-Type: application/json');

        echo json_encode($found);
    }
}
