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
                    case 'NRAI':
                        $cal = $this->readNRAICalendar($cal);
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

            $this->data = $this->removeOutOfRangeEvents(
                $this->data,
                time() - 86400,
                time() + (86400 * 365)
            );
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
        foreach ($data as $timestamp => $events) {
            if ($timestamp >= $start && $timestamp < $end) {
                $new_data[$timestamp] = $events;
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
        foreach ($gcal_events as $event) {
            foreach ($this->calculateGoogleTimestamps($event['when']) as $ts) {
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
     * Read the NRAI calendar.
     *
     * @param array $cal A calendar structure as decoded from the JSON file.
     *
     * @return array The passed-in calendar structure with an events list added
     *               to it.
     */
    private function readNRAICalendar($cal) {
        $data = $this->cachedURLFetch($cal['url']);

        // Parse the table out of the source using regular expressions. What
        // could possibly go wrong here?
        $data = preg_replace('/^.*<tbody.*?>(.*?)<\/tbody>.*$/s', '$1', $data);
        $data = preg_replace('/(<[A-Za-z]+)(?:\s+.*?)(>)/s', '$1$2', $data);
        $data = preg_replace('/<(?!\/?t[dr]).*?>/', '', $data);

        // Chop it up into tokens.
        $tokens = array_filter(
            array_map(
                'trim',
                preg_split(
                    '/(<.*?>)/',
                    $data,
                    -1,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                )
            )
        );

        // Transform the data into a 2D array
        $data = array();
        $row = 0;
        $col = 0;
        foreach ($tokens as $token) {
            switch ($token) {
                case '<tr>':
                case '<td>':
                    // Do nothing
                    break;
                case '</tr>':
                    $row++;
                    $col = 0;
                    break;
                case '</td>':
                    $col++;
                    break;
                default:
                    $data[$row][$col] = $token;
                    break;
            }
        }
        if (!preg_match('/\d+ Calendar/', $data[0][0])) {
            throw new Exception("Failed to parse NRAI calendar.");
        }

        $cal['events'] = array();
        $num_events = count($data) - 1;
        for ($i = 0; $i < $num_events; $i++) {
            foreach ($this->calculateNRAITimestamps($data[$i+1][0]) as $ts) {
                $cal['events'][] = array(
                    'timestamp' => $ts,
                    'date' => strftime('%Y-%m-%d', $ts),
                    'title' => $data[$i+1][1],
                    'location' => $data[$i+1][3],
                    'priority' => $cal['priority'],
                    'calendar' => $cal['name'],
                );
            }
        }

        return $cal;
    }

    /**
     * Convert NRAI formatted dates into timestamps.
     *
     * @param string $human_dates The date(s) as written on nrai.ie
     *
     * @return array An array of UNIX epoch times, one for the start of each
     *               day in the event.
     */
    public function calculateNRAITimestamps($human_dates) {
        $date_regex = '/^(\d+)(?:st|nd|rd|th)?(?:-(\d+)(?:st|nd|rd|th)?)?\s+(\w+)$/';
        if (preg_match($date_regex, trim($human_dates), $matches)) {
            $start = $matches[1];
            $end = $matches[2] ? ($matches[2] + 1) : ($start + 1);
            $month = $matches[3];
            $start = strftime("%Y-%m-%d", strtotime("$start $month"));
            $end = strftime("%Y-%m-%d", strtotime("$end $month"));
            return $this->calculateGoogleTimestamps(
                array(
                    array(
                        'start' => $start,
                        'end' => $end,
                    )
                )
            );
        } else {
            throw new Exception("$human_dates is not a valid date expression.");
        }
    }

    /**
     * Fetch a URL and cache the result if possible.
     *
     * @param string $url The URL to fetch.
     *
     * @return array The contents found at the URL.
     */
    private function cachedURLFetch($url) {
        $contents = null;
        $key = 'SHOOTING_IE_CALENDAR_' . md5($url);
        if (function_exists('apc_fetch')) {
            $contents = apc_fetch($key, $success);
            if (!$success) {
                $contents = null;
            }
        }
        if ($contents === null) {
            $contents = file_get_contents($url);
            if (function_exists('apc_store')) {
                apc_store($key, $contents, 3600);
            }
        }
        return $contents;
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
