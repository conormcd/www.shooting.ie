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

require_once __DIR__ . '/ExternalCalendarModelTestCase.php';

/**
 * Test the GoogleCalendarModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleCalendarModelTest extends ExternalCalendarModelTestCase {
	/**
	 * Make sure that a one day, all day event only appears once and doesn't 
	 * have a time associated with it.
	 *
	 * @return void
	 */
	public function testOneDayAllDayEvent() {
		$now = time();
		$futuretime = $now + (86400 * 2);
		$seen = false;
		$calendar = $this->_generateSampleCalendar($futuretime);
		$events = $calendar->events();
		foreach ($events as $day => $days_events) {
			$this->assertTrue(
				(boolean)preg_match('/^\d\d\d\d-\d\d-\d\d$/', $day),
				"Day is badly formatted: $day"
			);
			foreach ($days_events as $key => $event) {
				if ($key == 'onedayalldayevent') {
					$this->assertFalse(
						$seen,
						"One day, all day event seen more than once"
					);
					$seen = true;
					$this->assertSame(
						'',
						$event['time'],
						"One day, all day events should not have times."
					);
				}
			}
		}
	}

	/**
	 * Generate a sample calendar to test. 
	 *
	 * @param int $futuretime A UNIX epoch timestamp in the future.
	 *
	 * @return GoogleCalendarModel The model for a sample calendar.
	 */
	protected function _generateSampleCalendar($futuretime) {
		return new GoogleCalendarModel(
			self::$_test_calendar_name,
			self::$_test_calendar_img,
			array(
				'data' => array(
					'items' => array(
						$this->_testEvent(
							'2012-01-01 00:00:00',
							'2012-01-02 00:00:00',
							'Event in the past'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 09:00:00', $futuretime),
							strftime('%Y-%m-%d 17:00:00', $futuretime),
							'Less than one day, no crossing'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 00:00:00', $futuretime),
							strftime('%Y-%m-%d 00:00:00', $futuretime + 86400),
							'One day, all-day event'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 00:00:00', $futuretime),
							strftime(
								'%Y-%m-%d 00:00:00',
								$futuretime + (86400 * 2)
							),
							'Two day, all-day event'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 00:00:00', $futuretime),
							strftime(
								'%Y-%m-%d 00:00:00',
								$futuretime + (86400 * 3)
							),
							'Three day, all-day event'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 23:00:00', $futuretime),
							strftime('%Y-%m-%d 01:00:00', $futuretime + 86400),
							'Less than one day, crossing'
						),
						$this->_testEvent(
							strftime('%Y-%m-%d 23:00:00', $futuretime),
							strftime(
								'%Y-%m-%d 01:00:00',
								$futuretime + (86400 * 2)
							),
							'Multi-day, with times'
						),
					)
				)
			)
		);
	}

	/**
	 * Helper for GoogleCalendarModelTest::_generateSampleCalendar() for an 
	 * event in the calendar.
	 *
	 * @param string $start The start time of the event in YYYY-MM-DD HH:MM:SS.
	 * @param string $end   The end time of the event in YYYY-MM-DD HH:MM:SS.
	 * @param string $title The title of the event.
	 *
	 * @return array	An event in the format returned by Google Calendar after 
	 *					JSON decoding.
	 */
	protected function _testEvent($start, $end, $title) {
		return array(
			'when' => array(array('start' => $start, 'end' => $end),),
			'title' => $title,
			'details' => $title,
			'location' => self::$_test_location,
		);
	}
}

?>
