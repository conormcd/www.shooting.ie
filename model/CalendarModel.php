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

/**
 * Model for calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarModel extends JSONModel {
	/**
	 * Get the upcoming events from the calendars. 
	 *
	 * @return array The events from all the calendars, grouped by day.
	 */
	public function events() {
		// Get all the events and merge them.
		$events = array();
		foreach ($this->_data as $cal => $calendar) {
			$new_events = array();
			if ($calendar['type'] === 'Google') {
				$cal = new GoogleCalendarModel(
					$calendar['name'],
					$calendar['img'],
					$calendar['data']
				);
				$new_events = $cal->events();
			} else if ($calendar['type'] === 'NRAI') {
				$cal = new NRAICalendarModel(
					$calendar['name'],
					$calendar['img'],
					$calendar['data']
				);
				$new_events = $cal->events();
			} else {
				throw new Exception("Unknown type: {$calendar['type']}");
			}

			foreach ($new_events as $day => $evs) {
				foreach ($evs as $key => $event) {
					$events[$day][$key] = $event;
				}
			}
		}

		return $events;
	}

	/**
	 * Load the data from the configured data URLs.
	 *
	 * @return array The raw events from all the configured calendars.
	 */
	protected function _load() {
		$calendars = $this->_cache->get('calendars');
		if ($calendars === null) {
			// Get the calendar specs
			$metaModel = new CalendarMetaModel();
			$calendars = $metaModel->calendars();

			// Sort the calendar by priority, then name.
			$priority_sort = function($foo, $bar) {
				if ($foo['priority'] == $bar['priority']) {
					return 1;
				}
				return ($foo['priority'] < $bar['priority']) ? 1 : -1;
			};
			ksort($calendars);
			uasort($calendars, $priority_sort);

			// Fetch the contents of all the calendars
			foreach ($calendars as $file => $calendar) {
				if ($calendar['type'] == 'Google') {
					$calendars[$file]['data'] = $this->_readJSON($calendar['url']);
				} else if ($calendar['type'] == 'NRAI') {
					$calendars[$file]['data'] = file_get_contents($calendar['url']);
				} else {
					throw new Exception("Bad calendar type: {$calendar['type']}");
				}
			}

			// Cache the result.
			$this->_cache->set('calendars', $calendars, 3600);
		}
		return $calendars;
	}
}

?>
