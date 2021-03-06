<?php

require_once __DIR__ . '/../../lib/global.php';

/**
 * A wrapper for reading and merging remote calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Calendars
extends Feed
{
    /** A regular expression matching unwanted bits in an event title. */
    const CRUFT = '/\W*(?:postponed|cancell?ed|confirmed|provisional)\W*$/i';

    /** A cache for strtotime. */
    private $strtotime_cache;

    /**
     * Initialise with a directory which will contain a .json file for each
     * calendar.
     *
     * @param string $data_dir The path to the directory.
     * @param int    $time     The time of the request.
     */
    public function __construct($data_dir, $time) {
        parent::__construct($data_dir);
        $this->data = null;
        $this->time = ($time === null) ? time() : $time;
        $this->strtotime_cache = array();
    }

    /**
     * Get the raw data as encoded in the JSON files.
     *
     * @return array An array of events.
     */
    public function data() {
        if ($this->data === null) {
            $this->data = array();
            foreach ($this->dataFiles() as $file_path) {
                $cal = json_decode(file_get_contents($file_path), true);

                // Get the data for the type of calendar it is.
                switch ($cal['type']) {
                    case 'Google':
                        $cal = new GoogleCalendar($cal);
                        break;
                    case 'ICPSA':
                        $cal = new ICPSACalendar($cal);
                        break;
                    case 'NRAI':
                        $cal = new NRAICalendar($cal);
                        break;
                    default:
                        throw new Exception(
                            "Unknown calendar type: {$cal['type']}"
                        );
                }

                // Patch the data into the overall list of things we have.
                try {
                    foreach ($cal->events() as $event) {
                        $this->data[$event['date']][] = $event;
                    }
                } catch (Exception $e) {
                    // Ignore (but log) exceptions from that calendar.
                    ErrorHandler::handleException($e, false);
                }
            }

            $this->data = $this->removeOutOfRangeEvents(
                $this->data,
                $this->time - 86400,
                $this->time + (86400 * 365)
            );
            $this->data = $this->removeDuplicateEvents($this->data);
            $this->data = $this->removeCancelledEvents($this->data);
            $this->data = $this->cleanEventTitles($this->data);
            $this->data = $this->guessLocations($this->data);

            ksort($this->data);
        }
        return $this->data;
    }

    /**
     * Output data in iCalendar format.
     *
     * @return string The value of the data method, encoded in the iCalendar
     *                format.
     */
    public function ical() {
        $ical  = "BEGIN:VCALENDAR\n";
        $ical .= "VERSION:2.0\n";
        $ical .= "PRODID:-//www.shooting.ie//NONSGML v1.0//EN\n";
        foreach (array_values($this->data()) as $events) {
            foreach ($events as $event) {
                $ical .= ICalendarFormatter::formatEvent($event);
            }
        }
        $ical .= "END:VCALENDAR\n";
        return $ical;
    }

    /**
     * Determine if two events are similar enough to be merged.
     *
     * @param array $event_a An event structure.
     * @param array $event_b Another event structure.
     *
     * @return boolean True if the events are similar, false otherwise.
     */
    public function eventsSimilar($event_a, $event_b) {
        // Check if each of the titles (minus CRUFT) are equal or a prefix of
        // each other.
        $event_a = trim(preg_replace(self::CRUFT, '', $event_a['title']));
        $event_b = trim(preg_replace(self::CRUFT, '', $event_b['title']));
        if ($event_a === $event_b) {
            return true;
        } else if (strpos($event_a, $event_b) === 0) {
            return true;
        } else if (strpos($event_b, $event_a) === 0) {
            return true;
        }

        // Check if the titles just contain the same words
        $w_a = preg_split('/\s+/', preg_replace('/[^\w\s]/', '', $event_a));
        $w_b = preg_split('/\s+/', preg_replace('/[^\w\s]/', '', $event_b));
        sort($w_a);
        sort($w_b);
        if ($w_a == $w_b) {
            return true;
        }

        return false;
    }

    /**
     * Generate the set of unique pairs of numbers starting at $min and up to
     * but not including $max.
     *
     * @param int $min The inclusive lower bound of the range.
     * @param int $max The exclusive upper bound of the range.
     *
     * @return array An array of arrays, each inner array a unique pair.
     */
    public function uniquePairsInRange($min, $max) {
        $pairs = array();
        for ($i = $min; $i < $max; $i++) {
            for ($j = $min; $j < $max; $j++) {
                if ($i < $j) {
                    $pairs[] = array($i, $j);
                }
            }
        }
        return $pairs;
    }

    /**
     * Remove events that are not inside a specific date range.
     *
     * @param array $data  This will be $this->data
     * @param int   $start The inclusive lower bound of the time range.
     * @param int   $end   The inclusive lower bound of the time range.
     *
     * @return array A copy of $this->data with the out-of-range events
     *               removed.
     */
    public function removeOutOfRangeEvents($data, $start, $end) {
        $new_data = array();
        foreach ($data as $date => $events) {
            $timestamp = $this->strtotime($date);
            if ($timestamp >= $start && $timestamp < $end) {
                $new_data[$date] = $events;
            }
        }
        return $new_data;
    }

    /**
     * Remove events that are duplicated across multiple calendars.
     *
     * @param array $data This will be $this->data
     *
     * @return array A copy of $this->data with the duplicates removed.
     */
    public function removeDuplicateEvents($data) {
        $new_data = array();
        foreach ($data as $date => $events) {
            $num_events = count($events);

            // Detect the similarity
            $similar_pairs = array();
            foreach ($this->uniquePairsInRange(0, $num_events) as $p) {
                if ($this->eventsSimilar($events[$p[0]], $events[$p[1]])) {
                    $similar_pairs[] = $p;
                }
            }

            // Now choose the items to remove
            $removed_events = array();
            foreach ($similar_pairs as $similar_pair) {
                $a = $events[$similar_pair[0]];
                $b = $events[$similar_pair[1]];
                if ($a['priority'] < $b['priority']) {
                    $removed_events[] = $similar_pair[1];
                } else if ($b['priority'] < $a['priority']) {
                    $removed_events[] = $similar_pair[0];
                } else if ($a['calendar'] == $b['calendar']) {
                    $removed_events[] = $similar_pair[0];
                }
            }

            // Now filter the events
            for ($i = 0; $i < $num_events; $i++) {
                if (!in_array($i, $removed_events)) {
                    $new_data[$date][] = $events[$i];
                }
            }
        }
        return $new_data;
    }

    /**
     * Remove any events marked as cancelled.
     *
     * @param array $data This will be $this->data
     *
     * @return array A copy of $this->data with the cancelled events removed.
     */
    private function removeCancelledEvents($data) {
        $new_data = array();
        foreach ($data as $date => $events) {
            foreach ($events as $event) {
                if (!preg_match('/\W*cancelled\W*$/i', $event['title'])) {
                    $new_data[$date][] = $event;
                }
            }
        }
        return $new_data;
    }

    /**
     * Do some cosmetic cleaning of the event titles. There's some knowledge of
     * the habits of the calendar writers in here, for good or ill.
     *
     * @param array $data This will be $this->data
     *
     * @return array A copy of $this->data with the event titles cleaned.
     */
    public function cleanEventTitles($data) {
        foreach ($data as $date => $events) {
            $num_events = count($events);
            for ($i = 0; $i < $num_events; $i++) {
                $ev = $data[$date][$i];
                $ev['title_clean'] = preg_replace(self::CRUFT, '', $ev['title']);
                $ev['title_clean'] = preg_replace(
                    '/\s*\*\s*/',
                    '',
                    $ev['title_clean']
                );
                $data[$date][$i] = $ev;
            }
        }
        return $data;
    }

    /**
     * Guess the locations of events that don't have a location set.
     *
     * @param array $data This will be $this->data
     *
     * @return array A copy of $this->data with the event titles cleaned.
     */
    public function guessLocations($data) {
        foreach ($data as $date => $events) {
            $num_events = count($events);
            for ($i = 0; $i < $num_events; $i++) {
                $ev = $data[$date][$i];
                if (!$ev['location']) {
                    if (preg_match('/ @ (.*)$/', $ev['title'], $m)) {
                        $ev['location'] = $m[1];
                    }
                }
                $data[$date][$i] = $ev;
            }
        }
        return $data;
    }

    /**
     * A caching proxy for strtotime.
     *
     * @param string $str The string to pass to strtotime.
     *
     * @return The result of calling strtotime on $str. 
     */
    private function strtotime($str) {
        if (!array_key_exists($str, $this->strtotime_cache)) {
            $this->strtotime_cache[$str] = strtotime($str);
        }
        return $this->strtotime_cache[$str];
    }
}

?>
