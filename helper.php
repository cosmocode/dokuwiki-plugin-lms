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
     * @param string|null $user Username, null for no user data
     * @return array A list of lesson infos
     */
    public function getLessons($user = null)
    {
        $cp = $this->getConf('controlpage');
        $lessons = array_fill_keys($this->parseControlPage($cp), 0);
        if($user !== null) {
            $lessons = array_merge($lessons, $this->getUserLessons($user));
        }

        return $lessons;
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
     * @return int|false Either the lesson info or fals if given ID is not a lesson
     */
    public function getLesson($id, $user)
    {
        $all = $this->getLessons($user);
        return isset($all[$id]) ? $all[$id] : false;
    }

    /**
     * Get the next lesson relative to the given one
     *
     * @param string $id current lesson
     * @param null|string $user When user is given, next unseen lesson is returned
     * @return string
     */
    public function getNextLesson($id, $user=null) {
        $all = $this->getLessons($user);

        if(!isset($all[$id])) return false; // current page is not a lesson

        $keys = array_keys($all);
        $self = array_search($id, $keys);
        $len = count($keys);

        for($i=$self+1; $i < $len; $i++) {
            if($user !== null && $all[$keys[$i]] !== 0) {
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
    public function getPrevLesson($id, $user=null) {
        $all = $this->getLessons($user);

        if(!isset($all[$id])) return false; // current page is not a lesson

        $keys = array_keys($all);
        $self = array_search($id, $keys);

        for($i=$self-1; $i >= 0; $i--) {
            if($user !== null && $all[$keys[$i]] !== 0) {
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

