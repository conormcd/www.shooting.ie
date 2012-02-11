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
 * Test the CalendarMetaModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarMetaModelTest extends TestCase {
	/**
	 * Ensure that the CalendarMetaModel::calendars method returns well-formed 
	 * data.
	 *
	 * @return void
	 */
	public function testCalendarsReturnsValidInfo() {
		$cmm = new CalendarMetaModel();
		$calendars = $cmm->calendars();
		foreach ($calendars as $calendar) {
			$this->assertTrue(
				array_key_exists('name', $calendar),
				"Calendar without a name."
			);
			$this->assertTrue(
				array_key_exists('type', $calendar),
				"Calendar \"{$calendar['name']}\" has no type."
			);
			$this->assertTrue(
				in_array($calendar['type'], array('Google', 'NRAI')),
				"Invalid type for calendar \"{$calendar['name']}\""
			);
			$this->assertTrue(
				array_key_exists('priority', $calendar),
				"Calendar \"{$calendar['name']}\" has no priority."
			);
			$this->assertTrue(
				($calendar['priority'] >= 1),
				"Bad priority for calendar \"{$calendar['name']}\""
			);
			$this->assertTrue(
				array_key_exists('url', $calendar),
				"Calendar \"{$calendar['name']}\" has no URL."
			);
			$url_parsed = parse_url($calendar['url']);
			$this->assertTrue(
				($url_parsed !== false),
				"Failed to parse URL for calendar \"{$calendar['name']}\""
			);
			foreach (array('scheme', 'host', 'path') as $part) {
				$this->assertTrue(
					array_key_exists($part, $url_parsed),
					"Missing $part for calendar \"{$calendar['name']}\""
				);
			}
			$this->assertTrue(
				array_key_exists('img', $calendar),
				"Calendar \"{$calendar['name']}\" has no image."
			);
			$this->assertTrue(
				file_exists(ROOT . '/public' . $calendar['img']),
				"The image for calendar \"{$calendar['name']}\" doesn't exist"
			);
		}
	}
}

?>
