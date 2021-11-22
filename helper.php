<?php

/**
 * DokuWiki Plugin lms (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class helper_plugin_lms extends \dokuwiki\Extension\Plugin
{

    /**
     * Return all lessons and info about the user's current completion status
     *
     * @param null $user Username, null for current user
     * @return array[] A list of lesson infos
     */
    public function getLessons($user = null)
    {
        $cp = $this->getConf('controlpage');
        $lessons = array_fill_keys($this->parseControlPage($cp), 0);
        $lessons = array_merge($lessons, $this->getUserLessons($user));

        return $lessons;
    }

    /**
     * @param string $id Page ID of the lesson
     * @param bool $seen Mark as seen or unseen
     * @param null|string $user Username, null for current user
     * @return bool
     */
    public function markLesson($id, $seen = true, $user = null)
    {
        global $INPUT;
        if ($user === null) {
            $user = $INPUT->server->str('REMOTE_USER', null);
        }
        if ($user === null) return false;

        $file = $this->getUserFile($user);
        $line = time() . "\t" . $id . "\t" . ($seen ? 1 : 0) . "\n";
        return io_saveFile($file, $line, true);
    }

    /**
     * Get the list of completed lessons for a user
     *
     * This skips all lessons that used to be seen but have been marked unseen later
     *
     * @param string $user
     * @return array
     */
    public function getUserLessons($user)
    {
        $file = $this->getUserFile($user);
        if (!file_exists($file)) return [];

        $lessons = [];
        $lines = file($file);
        foreach ($lines as $line) {
            list($time, $id, $seen) = explode("\t", trim($line));

            // we use simple log files
            if ($seen) {
                $lessons[$id] = $time;
            } elseif (isset($lessons[$id])) {
                // an already seen lesson might have been marked unseen later
                unset($lessons[$id]);
            }
        }

        return $lessons;
    }

    /**
     * Get Info of a single lesson
     *
     * @param string $id Page ID of the lesson
     * @return array|false Either the lesson info or fals if given ID is not a lesson
     */
    public function getLesson($id)
    {
        $all = $this->getLessons();
        return $all[$id] ?: false;
    }

    /**
     * Get the filename used for storing lesson completions
     *
     * @param string $user username
     */
    protected function getUserFile($user)
    {
        global $conf;

        // we're not using cache files but our own meta directory
        $user = utf8_encodeFN($user); // make sure the user is clean for directories
        return $conf['metadir'] . '_lms/' . $user . '.lms';
    }

    /**
     * Get a list of links from the given control page
     *
     * @param string $cp The control page
     * @return array
     */
    protected function parseControlPage($cp)
    {
        $pages = [];

        $instructions = p_cached_instructions(wikiFN($cp), false, $cp);
        if ($instructions === null) return [];

        foreach ($instructions as $instruction) {
            if ($instruction[0] !== 'internallink') continue;
            $link = cleanID($instruction[1][0]);
            $pages[] = $link;
        }

        $pages = array_values(array_unique($pages));
        return $pages;
    }
}

