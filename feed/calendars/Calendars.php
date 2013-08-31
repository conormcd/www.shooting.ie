<?php

require_once dirname(__DIR__) . '/Feed.php';

/**
 * A wrapper for reading and merging remote calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Calendars
extends Feed
{
    /** A regular expression matching unwanted bits in an event title. */
    const CRUFT = '/\s*\*?\s*(?:postponed|cancell?ed|confirmed)\s*\*?\s*$/i';

    /**
     * Initialise with a directory which will contain a .json file for each 
     * calendar.
     *
     * @param string $data_dir The path to the directory.
     */
    public function __construct($data_dir) {
        parent::__construct($data_dir);
        $this->data = null;
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
                        $cal = $this->readGoogleCalendar($cal);
                        break;
                    default:
                        throw new Exception(
                            "Unknown calendar type: {$cal['type']}"
                        );
                }

                // Patch the data into the overall list of things we have.
                foreach ($cal['events'] as $event) {
                    $this->data[$event['timestamp']][] = $event;
                }
            }

            $this->data = $this->removeDuplicateEvents($this->data);
            $this->data = $this->removeCancelledEvents($this->data);

            ksort($this->data);
        }
        return $this->data;
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
        $event_a = trim(preg_replace(self::CRUFT, '', $event_a['title']));
        $event_b = trim(preg_replace(self::CRUFT, '', $event_b['title']));
        if ($event_a === $event_b) {
            return true;
        } else if (strpos($event_a, $event_b) === 0) {
            return true;
        } else if (strpos($event_b, $event_a) === 0) {
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
     * Remove events that are duplicated across multiple calendars.
     *
     * @param array $data This will be $this->data
     *
     * @return array A copy of $this->data with the duplicates removed.
     */
    public function removeDuplicateEvents($data) {
        $new_data = array();
        foreach ($data as $timestamp => $events) {
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
                    $new_data[$timestamp][] = $events[$i];
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
        foreach ($data as $timestamp => $events) {
            foreach ($events as $event) {
                if (!preg_match('/\W*cancelled\W*$/i', $event['title'])) {
                    $new_data[$timestamp][] = $event;
                }
            }
        }
        return $new_data;
    }

    /**
     * Read a Google calendar and augment the provided structure with a list of 
     * events.
     *
     * @param array $cal A calendar structure as decoded from the JSON file.
     *
     * @return array The passed-in calendar structure with an events list added 
     *               to it.
     */
    private function readGoogleCalendar($cal) {
        $gcal_data = $this->cachedJSONFetch($cal['url']);

        $gcal_events = array();
        if (array_key_exists('data', $gcal_data)) {
            if (array_key_exists('items', $gcal_data['data'])) {
                $gcal_events = $gcal_data['data']['items'];
            }
        }

        $cal['events'] = array();
        $cutoff_start = time() - 86400;
        $cutoff_end = time() + (86400 * 365);
        foreach ($gcal_events as $event) {
            foreach ($this->calculateGoogleTimestamps($event['when']) as $ts) {
                if ($ts > $cutoff_start && $ts < $cutoff_end) {
                    $cal['events'][] = array(
                        'timestamp' => $ts,
                        'date' => strftime('%Y-%m-%d', $ts),
                        'title' => $event['title'],
                        'details' => $event['details'],
                        'status' => $event['status'],
                        'location' => $event['location'],
                        'priority' => $cal['priority'],
                        'calendar' => $cal['name'],
                    );
                }
            }
        }

        return $cal;
    }

    /**
     * From a Google Calendar "when" expression, calculate a list of 
     * appropriate UNIX timestamps for the start of the event.
     *
     * @param array $when A Google Calendar "when" field.
     *
     * @return array An array of UNIX epoch timestamps.
     */
    private function calculateGoogleTimestamps($when) {
        $timestamps = array();
        foreach ($when as $period) {
            $start = strtotime($period['start']);
            $end = strtotime($period['end']);
            for ($t = $start; $t < $end; $t += 86400) {
                $timestamps[] = $t;
            }
        }
        return $timestamps;
    }

    /**
     * Fetch a JSON URL and cache the result if possible.
     *
     * @param string $url The URL to fetch.
     *
     * @return array The decoded form of the JSON fetched from the URL.
     */
    private function cachedJSONFetch($url) {
        $contents = null;
        $key = 'SHOOTING_IE_CALENDAR_' . md5($url);
        if (function_exists('apc_fetch')) {
            $contents = apc_fetch($key, $success);
            if (!$success) {
                $contents = null;
            }
        }
        if ($contents === null) {
            $contents = json_decode(file_get_contents($url), true);
            if ($contents === null) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $contents;
                }
            }
            if (function_exists('apc_store')) {
                apc_store($key, $contents, 3600);
            }
        }
        return $contents;
    }
}

?>
