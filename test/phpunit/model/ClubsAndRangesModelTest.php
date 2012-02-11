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
 * Test the ClubsAndRangesModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRangesModelTest extends TestCase {
	/**
	 * Ensure that the ClubsAndRangesModel::clubs() method returns well-formed 
	 * data.
	 *
	 * @return void
	 */
	public function testClubsReturnsValidInfo() {
		$model = new ClubsAndRangesModel();
		$clubs = $model->clubs();
		foreach ($clubs as $club) {
			$this->assertTrue(
				array_key_exists('id', $club),
				"Club without an ID."
			);
			$this->assertTrue(
				array_key_exists('latitude', $club),
				"Club \"{$club['id']}\" has no latitude."
			);
			$this->assertTrue(
				($club['latitude'] >= -90 && $club['latitude'] <= 90),
				"Latitude for\"{$club['id']}\" is out of bounds."
			);
			$this->assertTrue(
				array_key_exists('longitude', $club),
				"Club \"{$club['id']}\" has no longitude."
			);
			$this->assertTrue(
				($club['longitude'] >= -180 && $club['longitude'] <= 180),
				"Longitude for\"{$club['id']}\" is out of bounds."
			);
		}
	}
}

?>
