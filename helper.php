<?php

use dokuwiki\plugin\controlpage\ControlPage;
use dokuwiki\plugin\controlpage\Page;

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
     * @param string|null $user Username, null for no user data
     * @return Page[] A list of lesson infos
     */
    public function getLessons($user = null)
    {
        $cp = $this->getControlPage();
        if (!$cp) return [];

        try {
            $control = new ControlPage($cp, ControlPage::FLAG_NOEXTERNAL);
        } catch (\RuntimeException $e) {
            return [];
        }
        $lessons = $control->getAll();

        if ($user !== null) {
            foreach ($this->getUserLessons($user) as $page => $done) {
                if (isset($lessons[$page])) {
                    $lessons[$page]->setProperty('lms', $done);
                }
            }
        }

        return $lessons;
    }

    /**
     * Find the nearest controlpage
     *
     * @return false|string
     */
    public function getControlPage()
    {
        global $ID;
        global $INFO;

        $cp = $this->getConf('controlpage');
        $oldid = $ID;
        $ID = $INFO['id'] ?? $ID;
        $cp = page_findnearest($cp, false);
        $ID = $oldid;
        return $cp;
    }

    /**
     * @param string $id Page ID of the lesson
     * @param bool $seen Mark as seen or unseen
     * @param string $user Username
     * @return bool
     */
    public function markLesson($id, $user, $seen = true)
    {
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
     * Get Seen-Info of a single lesson
     *
     * @param string $id Page ID of the lesson
     * @param string $user User
     * @return int|false Either the lesson info or false if given ID is not a lesson
     */
    public function getLesson($id, $user)
    {
        $all = $this->getLessons($user);
        return isset($all[$id]) ? $all[$id]->getProperty('lms', 0) : false;
    }

    /**
     * Get the next lesson relative to the given one
     *
     * @param string $id current lesson
     * @param null|string $user When user is given, next unseen lesson is returned
     * @return string
     */
    public function getNextLesson($id, $user = null)
    {
        $all = $this->getLessons($user);

        if (!isset($all[$id])) return false; // current page is not a lesson

        $keys = array_keys($all);
        $self = array_search($id, $keys);
        $len = count($keys);

        for ($i = $self + 1; $i < $len; $i++) {
            if ($user !== null && $all[$keys[$i]]->getProperty('lms', 0) !== 0) {
                continue; // next element has already been seen by user
            }
            return $keys[$i];
        }

        // no more lessons
        return false;
    }

    /**
     * Get the previous lesson relative to the given one
     *
     * @param string $id current lesson
     * @param null|string $user When user is given, previous unseen lesson is returned
     * @return string
     */
    public function getPrevLesson($id, $user = null)
    {
        $all = $this->getLessons($user);

        if (!isset($all[$id])) return false; // current page is not a lesson

        $keys = array_keys($all);
        $self = array_search($id, $keys);

        for ($i = $self - 1; $i >= 0; $i--) {
            if ($user !== null && $all[$keys[$i]]->getProperty('lms', 0) !== 0) {
                continue; // next element has already been seen by user
            }
            return $keys[$i];
        }

        // no more lessons
        return false;
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
}

