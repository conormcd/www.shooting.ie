<?php

require_once __DIR__ . '/Calendar.php';

/**
 * A representation of Google Calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleCalendar
extends Calendar
{
    /**
     * Pull the events out of a Google calendar.
     *
     * @return array An array of events contained in the calendar.
     */
    public function events() {
        $gcal_data = $this->cachedJSONFetch($this->url);

        $gcal_events = array();
        if (array_key_exists('data', $gcal_data)) {
            if (array_key_exists('items', $gcal_data['data'])) {
                $gcal_events = $gcal_data['data']['items'];
            }
        }

        $events = array();
        foreach ($gcal_events as $event) {
            if (array_key_exists('when', $event)) {
                foreach ($this->days($event['when']) as $ts) {
                    $events[] = array(
                        'timestamp' => $ts,
                        'date' => strftime('%Y-%m-%d', $ts),
                        'title' => $event['title'],
                        'details' => $event['details'],
                        'status' => $event['status'],
                        'location' => $event['location'],
                        'priority' => $this->priority,
                        'calendar' => $this->name,
                    );
                }
            }
        }

        return $events;
    }

    /**
     * From a Google Calendar "when" expression, calculate a list of
     * appropriate UNIX timestamps for the start of the event.
     *
     * @param array $when A Google Calendar "when" field.
     *
     * @return array An array of UNIX epoch timestamps.
     */
    private function days($when) {
        $timestamps = array();
        foreach ($when as $period) {
            $timestamps = array_merge(
                $timestamps,
                $this->daysInRange(
                    strtotime($period['start']),
                    strtotime($period['end'])
                )
            );
        }
        return $timestamps;
    }
}

?>
