<?php

require_once __DIR__ . '/../../../feed/calendars/ICPSACalendar.php';

/**
 * Test the ICPSA calendar.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ICPSACalendarTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Test the months function output format.
     *
     * @return void
     */
    public function testMonths() {
        $cal = $this->createTestCalendar();
        $months = $cal->months();
        $this->assertEquals(12, count($months));
        foreach ($months as $month) {
            $this->assertEquals(2, count($month));
            $this->assertRegExp('/^\d{4}$/', $month[0]);
            $this->assertRegExp('/^\d{2}$/', $month[1]);
        }
    }

    /**
     * Ensure that the months function outputs ever-increasing values.
     *
     * @return void
     */
    public function testMonthsIncreases() {
        $cal = $this->createTestCalendar();
        $months = $cal->months();
        $cur_year = null;
        $cur_month = null;
        foreach ($months as $month) {
            $year = $months[0];
            $month = preg_replace('/^0/', '', $month[1]);
            if ($cur_year && $cur_month) {
                $this->assertGreaterThanOrEqual($cur_year, $year);
                if ($cur_month < 12) {
                    $this->assertGreaterThan($cur_month, $month);
                } else {
                    $this->assertEquals(1, $month);
                }
            }
            $cur_year = $year;
            $cur_month = $month;
        }
    }

    /**
     * Check that the URL formatter produces a valid URL.
     *
     * @return void
     */
    public function testFormatURL() {
        $cal = $this->createTestCalendar();
        $this->assertTrue(
            (false !== parse_url($cal->formatURL('2013', '09')))
        );
    }

    /**
     * Test that parseHTML successfully parses the HTML in all of the test 
     * fixture copies of the ICPSA calendar.
     *
     * @return void
     */
    public function testParseHTML() {
        $files = array();
        $fixtures_dir = __DIR__ . '/../../fixtures/calendars/default/icpsa';
        foreach (scandir($fixtures_dir) as $filename) {
            if ($filename[0] !== '.') {
                $files[] = realpath($fixtures_dir . '/' . $filename);
            }
        }

        $cal = $this->createTestCalendar();
        foreach ($files as $file) {
            $events = $cal->parseHTML(file_get_contents($file));
            foreach ($events as $day => $day_events) {
                $this->assertLessThan(32, $day);
                foreach ($day_events as $event) {
                    $this->assertArrayHasKey('url', $event);
                    $this->assertArrayHasKey('title', $event);
                }
            }
        }
    }

    /**
     * Create a test calendar to operate on in the tests.
     *
     * @param string $url The URL to use for fetching from.
     *
     * @return ICPSACalendar An instance of ICPSACalendar to test.
     */
    public function createTestCalendar($url = null) {
        return new ICPSACalendar(
            array(
                'name' => 'ICPSA',
                'url' => $url,
                'priority' => 1
            )
        );
    }
}

?>
