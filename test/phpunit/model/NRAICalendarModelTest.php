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
 * Test the NRAICalendarModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NRAICalendarModelTest extends ExternalCalendarModelTestCase {
	/**
	 * Generate a sample calendar for testing the parser.
	 *
	 * @param int $futuretime A UNIX epoch timestamp some time in the future.
	 *
	 * @return NRAICalendarModel A sample calendar to work on.
	 */
	protected function _generateSampleCalendar($futuretime) {
		$location = self::$_test_location;
		$oneday_day = strftime('%B %e', $futuretime);
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head><title>NRAI Test Calendar</title></head>
	<body>
		<table width="580" cellspacing="6" border="0" align="center">
			<tbody>
				<tr>
					<td valign="middle" bgcolor="#006351" align="center">
					<h3><strong>2012 Calendar </strong></h3>
					</td>
					<td valign="middle" bgcolor="#006351" align="center">
					<h3><strong>Event </strong></h3>
					</td>
					<td valign="middle" bgcolor="#006351" align="center">
					<h3><strong>Organiser</strong></h3>
					</td>
					<td valign="middle" bgcolor="#006351" align="center">
					<h3><strong>Range </strong></h3>
					</td>
				</tr>
				<tr>
					<td valign="middle" align="center">
					<p align="center">January 22</p>
					</td>
					<td valign="middle" align="center">F-TR Training</td>
					<td valign="middle" align="center">
					<p align="center">NRAI</p>
					</td>
					<td valign="middle" align="center">
					<p align="center">Tullamore</p>
					</td>
				</tr>
				<tr>
					<td valign="middle" align="center">
					<p align="center">$oneday_day</p>
					</td>
					<td valign="middle" align="center">One day event</td>
					<td valign="middle" align="center">
					<p align="center">NRAI</p>
					</td>
					<td valign="middle" align="center">
					<p align="center">$location</p>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
HTML;
		return new NRAICalendarModel(
			self::$_test_calendar_name,
			self::$_test_calendar_img,
			$html
		);
	}
}

?>
