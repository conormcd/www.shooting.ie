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
 * Meta-model for external calendars and the parsing thereof.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class ExternalCalendarModel extends Model {
	/** The name of the calendar. */
	protected $_name;

	/** The image to display beside events in the calendar. */
	protected $_image;

	/** The events in the calendar. */
	protected $_events;

	/**
	 * Set up the calendar and trigger a load.
	 *
	 * @param string $name  The name of the calendar.
	 * @param string $image The image to display beside events.
	 */
	public function __construct($name, $image) {
		$this->_name = $name;
		$this->_image = $image;
		parent::__construct();
	}

	/**
	 * Get the events from the calendar.
	 *
	 * @return array The calendar's events in the format expected by 
	 *               CalendarModel.
	 */
	public function events() {
		return $this->_events;
	}
}

?>
