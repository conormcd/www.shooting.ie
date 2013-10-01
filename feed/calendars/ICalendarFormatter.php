<?php

require_once __DIR__ . '/../../lib/global.php';

/**
 * A helper for formatting events into iCalendar style text.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ICalendarFormatter {
    /**
     * Format our internal/JSON event structure into a string containing a
     * valid iCalendar VEVENT record.
     *
     * @param array $event The event to format.
     *
     * @return string A valid iCalendar VEVENT string.
     */
    public static function formatEvent($event) {
        $vevent = array();

        // Construct the UID
        $vevent['UID'] = '';
        foreach ($event as $key => $value) {
            $vevent['UID'] .= ":$key:$value";
        }
        $vevent['UID'] = md5($vevent['UID']) . '@shooting.ie';

        // Format the timestamps
        $ts_format = '%Y%m%dT%H%M%SZ';
        $vevent['DTSTAMP'] = strftime('%Y%m%dT%H0000Z');
        $vevent['DTSTART'] = strftime($ts_format, $event['timestamp']);
        $vevent['DTEND'] = strftime($ts_format, $event['timestamp'] + 86400);

        // Copy in the simple data
        $simple_data = array(
            'title' => 'SUMMARY',
            'details' => 'DESCRIPTION',
            'location' => 'LOCATION',
            'url' => 'URL',
        );
        foreach ($simple_data as $src => $dst) {
            if (array_key_exists($src, $event) && $event[$src]) {
                $vevent[$dst] = $event[$src];
            }
        }

        // Now format the event
        $retval = "BEGIN:VEVENT\n";
        foreach ($vevent as $label => $value) {
            $value = preg_replace('/[\r\n]+/', "\\n", $value);
            $retval .= "$label:$value\n";
        }
        $retval .= "END:VEVENT\n";

        return $retval;
    }
}

?>
