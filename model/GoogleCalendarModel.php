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
 * Model for Google Calendars.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleCalendarModel extends ExternalCalendarModel {
	/** The data returned by Google Calendar. */
	private $_gcal_data;

	/**
	 * Assemble the data we need.
	 *
	 * @param string $name      The name of this calendar.
	 * @param string $image     The image to display beside events.
	 * @param array  $gcal_data The data to parse for calendar events.
	 */
	public function __construct($name, $image, $gcal_data) {
		$this->_gcal_data = $gcal_data;
		parent::__construct($name, $image);
	}

	/**
	 * Format the data into the layout that CalendarModel expects.
	 *
	 * @return void
	 */
	protected function _load() {
		$now = time();
		$events = array();
		if (array_key_exists('data', $this->_gcal_data)) {
			foreach ($this->_gcal_data['data']['items'] as $item) {
				foreach ($item['when'] as $when) {
					$start = strtotime($when['start']);
					$end = strtotime($when['end']);
					if ($end > $now - 86400) {
						$times = $this->_generateTimeSlices($start, $end);
						foreach ($times as $timespec) {
							$key = preg_replace(
								'/\W/',
								'',
								strtolower($item['title'])
							);
							$events[$timespec[0]][$key] = array(
								'title' => $item['title'],
								'details' => $item['details'],
								'location' => $item['location'],
								'time' => $timespec[1],
								'img' => $this->_image,
								'calendar_name' => $this->_name,
								'has_extra' => (
									$item['details'] ||
									$item['location'] ||
									$timespec[1]
								),
							);
						}
					}
				}
			}
		}
		$this->_events = $events;
	}

	/**
	 * Break up the period between two UNIX epoch times into a series of chunks 
	 * no bigger than a day.
	 *
	 * @param int $start The UNIX timestamp for the start of the period.
	 * @param int $end   The UNIX timestamp for the end of the period.
	 *
	 * @return array An array of chunks, each chunk being an array where the 
	 *               first element is the formatted day and the second element 
	 *               represents the time period for that day.
	 */
	private function _generateTimeSlices($start, $end) {
		$day_fmt = '%Y-%m-%d';
		$time_fmt = '%H:%M';

		$s_d = strftime($day_fmt, $start);
		$e_d = strftime($day_fmt, $end);
		$s_t = strftime($time_fmt, $start);
		$e_t = strftime($time_fmt, $end);

		// < 1 day, no crossing day boundary
		if ($end - $start < 86400 && $s_d == $e_d) {
			return array(array($s_d, "$s_t - $e_t"));
		}

		// One day, all day
		if ($end - $start == 86400 && $s_t == '00:00') {
			return array(array($s_d, ''));
		}

		// Multi-day, midnight to midnight
		if ($s_t == '00:00' && $e_t == '00:00') {
			$days = array();
			for ($i = $start; $i < $end; $i += 86400) {
				$days[] = array(strftime($day_fmt, $i), '');
			}
			return $days;
		}

		// All other cases
		$days = array();
		for ($i = $start; $i < $end; $i += 86400) {
			$days[] = array(strftime($day_fmt, $i), '');
		}
		$days[0][1] = $s_t;
		$days[count($days) - 1][1] = $e_t;
		return $days;
	}
}

?>
