<?php

require_once __DIR__ . '/../../../feed/calendars/Calendars.php';

/**
 * Test the feed builder object for the calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarsTest
extends PHPUnit_Framework_TestCase
{
    private static $test_time = 1370041200; // 1 June 2013
    /**
     * Check that the data method returns some data in roughly the right form.
     *
     * @return void
     */
    public function testDataReturnsSomething() {
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $data = $calendars->data();
        $this->assertNotEmpty($data);
        foreach ($data as $timestamp => $events) {
            $this->assertGreaterThan(0, $timestamp);
            $this->assertNotEmpty($events);
        }
    }

    /**
     * Ensure that multiple calls to data() return the same result.
     *
     * @return void
     */
    public function testDataDoesntChange() {
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $this->assertEquals($calendars->data(), $calendars->data());
    }

    /**
     * Make sure that data() throws an exception if there's an unknown type of 
     * calendar referenced in the data directory.
     *
     * @return void
     */
    public function testDataFailsOnUnknownTypeOfCalendar() {
        $calendars = new Calendars($this->dataDir('unknown_type'), self::$test_time);
        try {
            $calendars->data();
        } catch (Exception $e) {
            $this->assertNotEmpty("$e");
            return;
        }
        $this->fail("Did not raise an exception.");
    }

    /**
     * Check that the dataFiles method returns some files.
     *
     * @return void
     */
    public function testDataFilesReturnsFiles() {
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $file_count = 0;
        foreach ($calendars->dataFiles() as $path) {
            $this->assertTrue(file_exists($path));
            $this->assertTrue(is_file($path));
            $this->assertTrue(is_readable($path));
            $file_count++;
        }
        $this->assertGreaterThan(0, $file_count);
    }

    /**
     * Ensure that the JSON method returns valid JSON.
     *
     * @return void
     */
    public function testJsonReturnsJSON() {
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $this->assertNotNull(json_decode($calendars->json()));
    }

    /**
     * Ensure that the JSON-P method returns wrapped JSON.
     *
     * @return void
     */
    public function testJsonPReturnsWrappedJSON() {
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $this->assertEquals(
            $calendars->jsonp('foo'),
            'foo(' . $calendars->json() . ')'
        );
    }

    /**
     * Test that eventsSimilar correctly identifies a collection of pairs where 
     * every pair is a similar pair.
     *
     * @return void
     */
    public function testEventsSimilarPositive() {
        $event_pairs = array(
            array(
                array('title' => 'Same'),
                array('title' => 'Same'),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Same '),
            ),
            array(
                array('title' => 'Same '),
                array('title' => 'Same'),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Same *CANCELLED*'),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Same postponed'),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Same * confirmed * '),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Same Same'),
            ),
            array(
                array('title' => 'Same Same'),
                array('title' => 'Same'),
            ),
        );
        
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        foreach ($event_pairs as $pair) {
            $this->assertTrue($calendars->eventsSimilar($pair[0], $pair[1]));
        }
    }

    /**
     * Test that eventsSimilar correctly identifies a collection of pairs where 
     * no pair is a similar pair.
     *
     * @return void
     */
    public function testEventsSimilarNegative() {
        $event_pairs = array(
            array(
                array('title' => 'Same'),
                array('title' => 'Different'),
            ),
            array(
                array('title' => 'Same'),
                array('title' => 'Different Same'),
            ),
            array(
                array('title' => 'Common prefix but different suffix'),
                array('title' => 'Common prefix but totally different suffix'),
            ),
        );
        
        $calendars = new Calendars($this->dataDir(), self::$test_time);
        foreach ($event_pairs as $pair) {
            $this->assertFalse($calendars->eventsSimilar($pair[0], $pair[1]));
        }
    }

    /**
     * Ensure that uniquePairsInRange produces the right results.
     *
     * @return void
     */
    public function testUniquePairsInRange() {
        $ranges = array(
            '0..0' => array(),
            '0..1' => array(),
            '0..2' => array(array(0, 1)),
            '0..3' => array(array(0, 1), array(0, 2), array(1, 2)),
        );

        $calendars = new Calendars($this->dataDir(), self::$test_time);
        foreach ($ranges as $range => $result) {
            $range = explode('..', $range);
            $this->assertEquals(
                $calendars->uniquePairsInRange($range[0], $range[1]),
                $result
            );
        }
    }

    /**
     * Try out the duplicate event remover.
     *
     * @return void
     */
    public function testRemoveDuplicateEvents() {
        $input = array(
            1234 => array(
                array(
                    'title' => 'Event A'
                ),
            ),
            1235 => array(
                array(
                    'title' => 'Event A'
                ),
                array(
                    'title' => 'Event B'
                ),
            ),
            1236 => array(
                array(
                    'title' => 'Event C',
                    'priority' => 2,
                ),
                array(
                    'title' => 'Event C',
                    'priority' => 1,
                ),
            ),
            1237 => array(
                array(
                    'title' => 'Event D',
                    'priority' => 1,
                ),
                array(
                    'title' => 'Event D',
                    'priority' => 2,
                ),
            ),
            1238 => array(
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
            ),
            1239 => array(
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'bar',
                ),
            ),
            1240 => array(
                array(
                    'title' => 'Event F',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
                array(
                    'title' => 'F Event',
                    'priority' => 2,
                    'calendar' => 'bar',
                ),
            ),
        );
        $output = array(
            1234 => array(
                array(
                    'title' => 'Event A'
                ),
            ),
            1235 => array(
                array(
                    'title' => 'Event A'
                ),
                array(
                    'title' => 'Event B'
                ),
            ),
            1236 => array(
                array(
                    'title' => 'Event C',
                    'priority' => 1,
                ),
            ),
            1237 => array(
                array(
                    'title' => 'Event D',
                    'priority' => 1,
                ),
            ),
            1238 => array(
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
            ),
            1239 => array(
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
                array(
                    'title' => 'Event E',
                    'priority' => 1,
                    'calendar' => 'bar',
                ),
            ),
            1240 => array(
                array(
                    'title' => 'Event F',
                    'priority' => 1,
                    'calendar' => 'foo',
                ),
            ),
        );

        $calendars = new Calendars($this->dataDir(), self::$test_time);
        $this->assertEquals($calendars->removeDuplicateEvents($input), $output);
    }

    /**
     * Calculate the path to the data directory.
     *
     * @param string $fixture_name The name of the subdirectory in the 
     *                             test/fixtures/calendars directory which you 
     *                             want to use for test data.
     *
     * @return string The path to the data directory.
     */
    private function dataDir($fixture_name='default') {
        return dirname(dirname(__DIR__)) . "/fixtures/calendars/$fixture_name";
    }
}

?>
