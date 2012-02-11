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
 * Model for the NRAI calendar.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NRAICalendarModel extends ExternalCalendarModel {
	/** The HTML pulled from nrai.ie. */
	private $_html;

	/**
	 * Get the stuff we need to parse.
	 *
	 * @param string $name      The name of this calendar.
	 * @param string $image     The image to display beside events.
	 * @param string $page_html The HTML to parse for calendar events.
	 */
	public function __construct($name, $image, $page_html) {
		$this->_html = $page_html;
		parent::__construct($name, $image);
	}

	/**
	 * Parse the HTML and store the events.
	 *
	 * @return void
	 */
	protected function _load() {
		try {
			$this->_events = $this->_generateEvents(
				$this->_name,
				$this->_image,
				$this->_extractTable($this->_html)
			);
		} catch (Exception $e) {
			// Ignore $e for the moment, should probably log it later
			$this->_events = array();
		}
	}

	/**
	 * Extract a 2D array from the table which contains the calendar.
	 *
	 * @param string $html The HTML of the full calendar page on nrai.ie
	 *
	 * @return array A 2D array which matches the data in the HTML table on 
	 *               nrai.ie.
	 */
	private function _extractTable($html) {
		// Pull out the table.
		$html = preg_replace(
			'/^.*<tbody.*?>/s',
			'<tbody>',
			preg_replace(
				'#</tbody>.*#s',
				'</tbody>',
				$html
			)
		);

		// Kill the attributes
		$html = preg_replace('/<(\w+)\s+.*?>/s', '<$1>', $html);

		// Get rid of tags we don't want.
		$html = preg_replace('/<(?!\/?t[dr]).*?>/', '', $html);

		// Break it into tokens around the tags.
		$tokens = preg_split(
			'/(<.*?>)/',
			$html,
			-1,
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
		);

		// Munge it into a 2D array
		$data = array();
		$row = 0;
		$col = 0;
		foreach ($tokens as $token) {
			$token = preg_replace('/&nbsp;/', ' ', $token);
			$token = html_entity_decode($token, ENT_QUOTES, 'UTF-8');
			$token = trim($token);
			if ($token) {
				switch ($token) {
					case '<tr>':
					case '<td>':
						/* Ignore */
						break;
					case '</tr>':
						$row++;
						$col = 0;
						break;
					case '</td>':
						$col++;
						break;
					default:
						$data[$row][$col] = $token;
				}
			}
		}

		// Rough checks to see that the data is OK
		if (!preg_match('/\d+ Calendar/', $data[0][0])) {
			throw new Exception("Table didn't start with [YEAR] Calendar.");
		}
		$enforce_row_size = function($value, $key) {
			if (count($value) != 4) {
				throw new Exception("Odd sized row found on row $key");
			}
		};
		array_walk($data, $enforce_row_size);

		// Remove the first row
		array_shift($data);

		return $data;
	}

	/**
	 * Format the events into the layout that CalendarModel expects.
	 *
	 * @param string $name  The name of the calendar.
	 * @param string $image The image to display beside events.
	 * @param array  $table The table from _extractTable.
	 *
	 * @return array The events in the format we need them in.
	 */
	private function _generateEvents($name, $image, $table) {
		$events = array();
		$now = time();
		foreach ($table as $row) {
			$timestamp = strtotime($row[0]);
			if ($timestamp == 0) {
				// Failed to parse the date.
				if (preg_match('/^\w+\s+\d+$/', $row[0])) {
					// It looks approximately right, it's probably a 
					// misspelling of a month.
					list($month, $day) = preg_split('/\s+/', $row[0]);
					$month = $this->_guessMonth($month);
					if ($month !== null) {
						$timestamp = strtotime("$month $day");
					}
				}
			}
			if ($timestamp - $now > 0) {
				$date = strftime('%Y-%m-%d', $timestamp);
				$event_titles = preg_split("/(?:\r\n|\r|\n)/", $row[1]);
				foreach ($event_titles as $event_title) {
					$key = preg_replace('/\W/', '', strtolower($event_title));
					$events[$date][$key] = array(
						'title' => trim($event_title),
						'details' => '',
						'location' => $row[3],
						'time' => '',
						'img' => $image,
						'calendar_name' => $name,
						'has_extra' => $row[3],
					);
				}
			}
		}

		return $events;
	}

	/**
	 * Given a misspelled month, attempt to correct it.
	 *
	 * @param string $month The misspelled month.
	 *
	 * @return The correct month or null if it can't be guessed.
	 */
	private function _guessMonth($month) {
		static $months = array(
			'Jan',
			'January',
			'Feb',
			'February',
			'Mar',
			'March',
			'Apr',
			'April',
			'May',
			'May',
			'Jun',
			'June',
			'Jul',
			'July',
			'Aug',
			'August',
			'Sep',
			'September',
			'Oct',
			'October',
			'Nov',
			'November',
			'Dec',
			'December'
		);
		$scored = array();
		foreach ($months as $m) {
			$score = levenshtein($m, $month) / strlen($month);
			$scored[$m] = $score;
		}
		asort($scored);
		foreach ($scored as $m => $score) {
			if ($score < 0.25) {
				return $m;
			}
		}
		return null;
	}
}

?>
