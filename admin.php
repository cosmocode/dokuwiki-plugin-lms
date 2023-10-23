<?php

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\Form\Form;

/**
 * DokuWiki Plugin lms (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class admin_plugin_lms extends AdminPlugin
{
    /** @inheritDoc */
    public function forAdminOnly()
    {
        return false;
    }

    /** @inheritDoc */
    public function handle()
    {
        // FIXME data processing
    }

    /** @inheritDoc */
    public function html()
    {
        global $INPUT;

        echo '<h1>' . $this->getLang('menu') . '</h1>';

        $form = new Form(['method' => 'POST', 'id' => 'lms__admin-autocomplete']);
        $form->addTextInput('user', $this->getLang('username'));
        $form->addButton('submit', '🔍');
        echo '<p>' . $form->toHTML() . '</p>';

        if (!$INPUT->str('user')) return;

        /** @var helper_plugin_lms $hlp */
        $hlp = $this->loadHelper('lms');
        $list = $hlp->getUserLessons($INPUT->str('user'));

        echo sprintf('<h2>' . $this->getLang('status') . '</h2>', hsc($INPUT->str('user')));
        echo '<table class="inline">';
        foreach ($list as $id => $dt) {
            echo '<tr>';
            echo '<td>';
            echo html_wikilink($id);
            echo '</td>';
            echo '<td>';
            if ($dt) {
                echo dformat($dt);
            } else {
                echo '---';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
