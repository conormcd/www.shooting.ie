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
                'start' => '2013-06-01',
                'end' => '2013-06-01',
                'output' => array()
            ),
            array(
                'start' => '2013-06-01',
                'end' => '2013-06-02',
                'output' => array('2013-06-01')
            ),
            array(
                'start' => '2012-12-30',
                'end' => '2013-01-02',
                'output' => array('2012-12-30', '2012-12-31', '2013-01-01')
            ),
        );

        foreach ($test_data as $test_scenario) {
            $days = $calendar->daysInRange(
                strtotime($test_scenario['start']),
                strtotime($test_scenario['end'])
            );
            $this->assertEquals(
                array_map(
                    function ($day) {
                        return strftime('%Y-%m-%d', $day);
                    },
                    $days
                ),
                $test_scenario['output']
            );
        }
    }

    /**
     * Test the basic functionality of cachedFetch.
     *
     * @return void
     */
    public function testCachedFetch() {
        $calendar = $this->dummyCalendar();
        $result = $calendar->cachedFetch(
            function () {
                return 'data';
            },
            'key',
            1
        );
        $this->assertEquals('data', $result);
    }

    /**
     * Test that cachedFetch caches.
     *
     * @return void
     */
    public function testCachedFetchCaches() {
        $calendar = $this->dummyCalendar();
        $result = $calendar->cachedFetch(
            function () {
                throw new Exception('foo');
            },
            'key2',
            1
        );
        $this->assertNull($result);
    }

    /**
     * Test the failure functionality of cachedFetch.
     *
     * @return void
     */
    public function testCachedFetchFail() {
        $calendar = $this->dummyCalendar();
        $fetcher = function() {
            return rand();
        };
        $this->assertEquals(
            $calendar->cachedFetch($fetcher, 'key3', 5),
            $calendar->cachedFetch($fetcher, 'key3', 5)
        );
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
     * Test the failure functionality of cachedURLFetch.
     *
     * @return void
     */
    public function testCachedURLFetchFail() {
        $calendar = $this->dummyCalendar();
        $this->assertNull($calendar->cachedURLFetch('/' . md5(rand())));
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
     * Test a failure scenario in cachedJSONFetch().
     *
     * @return void
     */
    public function testCachedJSONFetchFail() {
        $calendar = $this->dummyCalendar();
        $this->assertEquals(
            null,
            $calendar->cachedJSONFetch(__FILE__)
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
