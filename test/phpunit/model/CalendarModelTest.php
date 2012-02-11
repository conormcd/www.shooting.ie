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
 * Test the CalendarModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarModelTest extends TestCase {
	/**
	 * Ensure that the CalendarModel::events method returns well-formed 
	 * data.
	 *
	 * @return void
	 */
	public function testEventsReturnsValidInfo() {
		// Find the valid calendar names
		$meta_model = new CalendarMetaModel();
		$calendar_names = array();
		foreach ($meta_model->calendars() as $calendar) {
			$calendar_names[] = $calendar['name'];
		}
		
		// Check the events
		$calendar = new CalendarModel();
		$events = $calendar->events();
		$calendar_names_seen = array();
		foreach ($events as $day => $day_events) {
			$this->assertTrue(strlen($day) > 0, "Invalid day key.");
			foreach ($day_events as $key => $event) {
				$this->assertTrue(
					(boolean)preg_match('/^\w+$/', $key),
					"Invalid key: $key"
				);
				$this->assertTrue(
					array_key_exists('title', $event),
					"Event with no title!"
				);
				$this->assertTrue(
					strlen($event['title']) > 0,
					"Event with empty title!"
				);
				$this->assertTrue(
					array_key_exists('img', $event),
					"Event \"{$event['title']}\" has no image"
				);
				$this->assertTrue(
					file_exists(ROOT . '/public' . $event['img']),
					"Event \"{$event['title']}\" has no image"
				);
				$this->assertTrue(
					array_key_exists('calendar_name', $event),
					"Event \"{$event['title']}\" has no calendar name."
				);
				$calendar_names_seen[] = $event['calendar_name'];
			}
		}

		// Make sure the calendar names are all OK.
		foreach ($calendar_names_seen as $name) {
			$this->assertTrue(
				in_array($name, $calendar_names),
				"Calendar \"$name\" isn't in the meta-model"
			);
		}
	}
}

?>
