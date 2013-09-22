<?php

require_once __DIR__ . '/../../../feed/calendars/GoogleCalendar.php';

/**
 * Test Google Calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleCalendarTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Test the events method with some fixtures.
     *
     * @return void
     */
    public function testEvents() {
        $cal_spec = __DIR__ . '/../../fixtures/calendars/default/gcal.json';
        $cal_spec = json_decode(file_get_contents($cal_spec), true);

        $cal = new GoogleCalendar($cal_spec);
        $events = $cal->events();
        $this->assertTrue(is_array($events));

        // Check the contents of the events.
        $mandatory_event_keys = array(
            'calendar',
            'date',
            'details',
            'location',
            'priority',
            'status',
            'timestamp',
            'title',
        );
        foreach ($events as $event) {
            foreach ($mandatory_event_keys as $key) {
                $this->assertTrue(array_key_exists($key, $event));
            }
            $this->assertEquals($cal_spec['name'], $event['calendar']);
            $this->assertLessThanOrEqual(
                86400,
                $event['timestamp'] - strtotime($event['date'])
            );
            $this->assertEquals($cal_spec['priority'], $event['priority']);
            $this->assertNotEmpty($event['status']);
            $this->assertEquals(
                $event['date'],
                strftime("%Y-%m-%d", $event['timestamp'])
            );
            $this->assertNotEmpty($event['title']);
        }
    }

    /**
     * Test parsing the Google Calendar "when" expressions.
     *
     * @return void
     */
    public function testDays() {
        $test_data = array(
            array(
                'input' => array(
                    array(
                        'start' => '2013-02-01',
                        'end' => '2013-02-01'
                    )
                ),
                'expected' => array()
            ),
            array(
                'input' => array(
                    array(
                        'start' => '2013-02-01',
                        'end' => '2013-02-02'
                    )
                ),
                'expected' => array(
                    '1359676800',
                )
            ),
            array(
                'input' => array(
                    array(
                        'start' => '2013-02-01',
                        'end' => '2013-02-03'
                    )
                ),
                'expected' => array(
                    '1359676800',
                    '1359763200'
                )
            ),
            array(
                'input' => array(
                    array(
                        'start' => '2013-02-01',
                        'end' => '2013-02-02'
                    ),
                    array(
                        'start' => '2013-02-02',
                        'end' => '2013-02-03'
                    ),
                ),
                'expected' => array(
                    '1359676800',
                    '1359763200'
                )
            ),
        ); 
        
        $cal = new GoogleCalendar(
            array(
                'name' => 'Test calendar',
                'priority' => 1,
                'url' => null
            )
        );
        foreach ($test_data as $test_case) {
            $this->assertEquals(
                $test_case['expected'],
                $cal->days($test_case['input'])
            );
        }
    }
}

?>
