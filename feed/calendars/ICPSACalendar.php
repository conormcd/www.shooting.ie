<?php

require_once __DIR__ . '/Calendar.php';

/**
 * A representation of the ICPSA calendar as published on their website.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ICPSACalendar
extends Calendar
{
    /**
     * Read the NRAI calendar.
     *
     * @return array An array of the events in the NRAI calendar.
     */
    public function events() {
        $events = array();
        foreach ($this->months() as $month) {
            $month_events = $this->parseHTML(
                $this->cachedURLFetch(
                    $this->formatURL($month[0], $month[1])
                )
            );
            if (count($month_events) > 0) {
                foreach ($month_events as $event_day => $day_events) {
                    $timestamp = strtotime(
                        sprintf(
                            '%4d-%02d-%02d',
                            $month[0],
                            $month[1],
                            $event_day
                        )
                    );
                    foreach ($day_events as $event) {
                        $events[] = array(
                            'timestamp' => $timestamp,
                            'date' => strftime('%Y-%m-%d', $timestamp),
                            'title' => $event['title'],
                            'location' => $event['location'],
                            'priority' => $this->priority,
                            'calendar' => $this->name,
                            'url' => $event['url'],
                        );
                    }
                }
            } else {
                break;
            }
        }
        return $events;
    }

    /**
     * Calculate the next N months.
     *
     * @param int $num_months The numbers of months to generate.
     *
     * @return array An array of arrays. The inner arrays are all of length 2 
     *               and contain the 4 digit year and 2 digit month in that 
     *               order.
     */
    public function months($num_months = 12) {
        $months = array();
        $time = time();
        $last_month = null;
        while (count($months) < $num_months) {
            $year = date('Y', $time);
            $month = date('m', $time);
            if ($last_month !== $month) {
                $months[] = array($year, $month);
            }
            $time += 86400;
            $last_month = $month;
        }
        return $months;
    }
    
    /**
     * Get a URL for a specific year and month of the ICPSA calendar. 
     *
     * @param int $year  The 4 digit year.
     * @param int $month The month, including leading 0.
     *
     * @return array The URL to fetch for the requested month of the calendar.
     */
    public function formatURL($year, $month) {
        $url = $this->url;
        $url = preg_replace('/%yyyy/', $year, $url);
        $url = preg_replace('/%mm/', $month, $url);
        return $url;
    }

    /**
     * Extract the events from the HTML on the ICPSA calendar page.
     *
     * @param string $html The HTML from a single ICPSA calendar page.
     *
     * @return A list of a events from the HTML.
     */
    public function parseHTML($html) {
        // The calendar HTMl is not well-formed so parsing it with any of the 
        // XML parsers is a waste of time.
        $result = array();

        // Get all of the days
        preg_match_all(
            '/<td>\s*<span class="day">\d*<\/span>.*?<\/td>/s',
            $html,
            $days
        );
        foreach ($days[0] as $day) {
            // Find the day number
            $day_num = preg_replace('/.*<span class="day">(\d+).*/s', '$1', $day);
            $day_num = preg_replace('/^0*/', '', $day_num);

            // Get all of the events
            preg_match_all('/<a class="event".*?<\/a>/', $day, $events);
            foreach ($events[0] as $event) {
                if (preg_match('/<a.*href="(.*?)".*?>(.*?)<\/a>/', $event, $m)) {
                    $location = '';
                    if (preg_match('/&#8211;/', $m[2])) {
                        $location = preg_split('/&#8211;/', $m[2]);
                        $location = trim($location[count($location)-1]);
                    }
                    $result[$day_num][] = array(
                        'url' => $m[1],
                        'title' => html_entity_decode($m[2]),
                        'location' => $location,
                    );
                }
            }
        }
        return $result;
    }
}

?>
