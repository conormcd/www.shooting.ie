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
 * View for /calendar
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarView extends View {
	/** Activate the calendar tab in the top bar. */
	public $activeTabCalendar = "active";

	/**
	 * Take the raw events from the model an rework them so that they can be 
	 * rendered in Mustache. Also, apply fix-ups to the data to make it 
	 * prettier.
	 *
	 * @return array See CalendarModel::events() for details.
	 */
	public function events() {
		$events = $this->_model->events();

		// Apply cosmetic fix-ups to the data
		foreach ($events as $day => $days_events) {
			foreach ($days_events as $key => $event) {
				// Deal with events marked as "provisional" (the NTSA likes 
				// doing this).
				if (preg_match('/provisional/i', $event['title'])) {
					$event['label'] = 'Provisional';
				}
				$event['title'] = $this->cleanEventTitle($event['title']);

				// Allow for large descriptions to be collapsed.
				$events[$day][$key] = $event;
			}
		}
		
		// Sort the events by key to make them line up by time.
		ksort($events);
		
		// Clump the events by day.
		$clumped_events = array();
		foreach ($events as $day => $days_events) {
			$clumped_events[] = array(
				'day' => strftime('%A, %e %b %Y', strtotime($day)),
				'day_events' => array_values($days_events)
			);
		}
		$events = $clumped_events;

		return $events;
	}

	/**
	 * Clean up event titles to remove extraneous formatting or words.
	 *
	 * @param string $title The title to clean.
	 *
	 * @return string The cleaned title.
	 */
	private function cleanEventTitle($title) {
		$title = preg_replace('/\*/', '', $title);
		$title = preg_replace('/provisional/i', '', $title);
		$title = preg_replace('/\s+/', ' ', $title);
		return trim($title);
	}
}

?>
