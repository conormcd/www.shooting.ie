<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

require_once __DIR__ . '/../TestCase.php';

/**
 * Test case for ExternalCalendarModel classes.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class ExternalCalendarModelTestCase extends TestCase {
	/** The name of the test calendar. */
	protected static $_test_calendar_name = 'Test Calendar';

	/** The image for the test calendar. */
	protected static $_test_calendar_img = '/img/test.png';

	/** The location for events. */
	protected static $_test_location = 'Test Location';

	/**
	 * Ensure that the basic, common data exists in all events.
	 *
	 * @return void
	 */
	public function testCommonDataExistsInAllEvents() {
		$futuretime = time() + (86400 * 2);
		$calendar = $this->_generateSampleCalendar($futuretime);
		$events = $calendar->events();
		foreach ($events as $day => $days_events) {
			$this->assertTrue(
				(boolean)preg_match('/^\d\d\d\d-\d\d-\d\d$/', $day),
				"Day is badly formatted: $day"
			);
			foreach ($days_events as $key => $event) {
				$this->assertTrue(
					(boolean)preg_match('/^\w+$/', $key),
					"Key is badly formatted: $key"
				);
				$required_fields = array(
					'calendar_name',
					'details',
					'has_extra',
					'img',
					'location',
					'time',
					'title',
				);
				foreach ($required_fields as $field) {
					$this->assertTrue(
						array_key_exists($field, $event),
						"Event does not have a $field."
					);
				}
				$this->assertSame(
					self::$_test_calendar_name,
					$event['calendar_name'],
					"Calendar name is not correct."
				);
				$this->assertSame(
					self::$_test_calendar_img,
					$event['img'],
					"Calendar image is not correct."
				);
				$this->assertSame(
					self::$_test_location,
					$event['location'],
					"Calendar location is not correct."
				);
			}
		}
	}
	
	/**
	 * Make sure that an event in the past does not get included.
	 *
	 * @return void
	 */
	public function testEventInThePastNotIncluded() {
		$now = time();
		$futuretime = $now + (86400 * 2);
		$calendar = $this->_generateSampleCalendar($futuretime);
		$events = $calendar->events();
		foreach ($events as $day => $days_events) {
			foreach ($days_events as $key => $event) {
				$this->assertNotSame(
					'eventinthepast',
					$key,
					'The sentinel event from the past was included.'
				);
				$times = explode(' - ', $event['time']);
				$this->assertTrue(
					strtotime("$day {$times[0]}") > $now,
					"An event from the past was included."
				);
			}
		}
	}

	/** 
	 * Generate a sample calendar for the tests to operate on.
	 *
	 * @param int $futuretime A UNIX epoch timestamp in the future.
	 *
	 * @return ExternalCalendarModel A sample calendar.
	 */
	protected abstract function _generateSampleCalendar($futuretime);
}

?>
