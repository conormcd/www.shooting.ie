<?php

require_once __DIR__ . '/../../../feed/calendars/Calendar.php';

/**
 * Test the parent class of all calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Make sure that the daysInRange function works as expected.
     *
     * @return void
     */
    public function testDaysInRange() {
        $calendar = $this->dummyCalendar();

        $test_data = array(
            array(
                'start' => '2013-06-01T12:00:00Z',
                'end' => '2013-06-01T12:00:00Z',
                'output' => array()
            ),
            array(
                'start' => '2013-06-01T12:00:00Z',
                'end' => '2013-06-02T12:00:00Z',
                'output' => array('2013-06-01')
            ),
            array(
                'start' => '2012-12-30T12:00:00Z',
                'end' => '2013-01-02T12:00:00Z',
                'output' => array('2012-12-30', '2012-12-31', '2013-01-01')
            ),
        );

        foreach ($test_data as $test_scenario) {
            $days = $calendar->daysInRange(
                strtotime($test_scenario['start']),
                strtotime($test_scenario['end'])
            );
            $this->assertEquals(
                $test_scenario['output'],
                array_map(
                    function ($day) {
                        return strftime('%Y-%m-%d', $day);
                    },
                    $days
                )
            );
        }
    }

    /**
     * Test the basic functionality of cachedURLFetch.
     *
     * @return void
     */
    public function testCachedURLFetch() {
        $calendar = $this->dummyCalendar();
        $this->assertEquals(
            $calendar->cachedURLFetch(__FILE__),
            file_get_contents(__FILE__)
        );
    }

    /**
     * Test cachedJSONFetch().
     *
     * @return void
     */
    public function testCachedJSONFetch() {
        $calendar = $this->dummyCalendar();
        $json_file  = dirname(dirname(__DIR__));
        $json_file .= '/fixtures/calendars/default/gcal.json';
        $this->assertTrue(file_exists($json_file));
        $this->assertEquals(
            $calendar->cachedJSONFetch($json_file),
            json_decode(file_get_contents($json_file), true)
        );
    }

    /**
     * Create a DummyCalendar pointing to a specific URL.
     *
     * @param string $url The URL or file path to point to.
     *
     * @return DummyCalendar A calendar to play with.
     */
    private function dummyCalendar($url='/dev/null') {
        return new DummyCalendar(
            array(
                'name' => 'Dummy calendar',
                'priority' => 1,
                'url' => $url
            )
        );
    }
}

/**
 * The simplest implementation of Calendar that could possibly work.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class DummyCalendar
extends Calendar
{
    /**
     * We have to implement this, so let's just make a dummy version.
     *
     * @return array An empty array.
     */
    public function events() {
        return array();
    }
}

?>
