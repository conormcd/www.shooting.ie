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
 * Test the ExternalCalendarModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ExternalCalendarModelTest extends TestCase {
	/**
	 * Simply test that calling events returns an array with some results in it.
	 *
	 * @return void
	 */
	public function testEventsReturnsSomeData() {
		$model = new ExternalCalendarModelImpl('foo', 'bar');
		$events = $model->events();
		$this->assertTrue(
			is_array($events) && count($events) > 0,
			"events() returned no data."
		);
	}
}

/**
 * Trial implementation of ExternalCalendarModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ExternalCalendarModelImpl extends ExternalCalendarModel {
	/**
	 * Load the fixed dummy data.
	 *
	 * @return void
	 */
	protected function _load() {
		$this->_events = array(
			'2012-01-01' => array(
				'foobarbaz' => array(
					'title' => 'Foo Bar Baz',
					'details' => 'Details of the event'
				)
			)
		);
	}
}

?>
