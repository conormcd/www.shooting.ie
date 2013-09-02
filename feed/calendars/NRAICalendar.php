<?php

require_once __DIR__ . '/Calendar.php';

/**
 * A representation of the NRAI calendar as published on their website.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NRAICalendar
extends Calendar
{
    /**
     * Read the NRAI calendar.
     *
     * @return array An array of the events in the NRAI calendar.
     */
    public function events() {
        $data = $this->cachedURLFetch($this->url);

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

        $events = array();
        $num_events = count($data) - 1;
        for ($i = 0; $i < $num_events; $i++) {
            foreach ($this->days($data[$i+1][0]) as $ts) {
                $events[] = array(
                    'timestamp' => $ts,
                    'date' => strftime('%Y-%m-%d', $ts),
                    'title' => $data[$i+1][1],
                    'location' => $data[$i+1][3],
                    'priority' => $this->priority,
                    'calendar' => $this->name,
                );
            }
        }

        return $events;
    }

    /**
     * Convert NRAI formatted dates into timestamps.
     *
     * @param string $human_dates The date(s) as written on nrai.ie
     *
     * @return array An array of UNIX epoch times, one for the start of each
     *               day in the event.
     */
    private function days($human_dates) {
        $date_regex = '/^(\d+)(?:st|nd|rd|th)?(?:-(\d+)(?:st|nd|rd|th)?)?\s+(\w+)$/';
        if (preg_match($date_regex, trim($human_dates), $matches)) {
            $start = $matches[1];
            $end = $matches[2] ? ($matches[2] + 1) : ($start + 1);
            $month = $matches[3];
            return $this->daysInRange(
                strtotime("$start $month"),
                strtotime("$end $month")
            );
        } else {
            throw new Exception("$human_dates is not a valid date expression.");
        }
    }
}

?>
