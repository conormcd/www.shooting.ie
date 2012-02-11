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
 * Test the OrganisationsModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class OrganisationsModelTest extends TestCase {
	/**
	 * Ensure that the OrganisationsModel::organisations() method returns 
	 * well-formed data.
	 *
	 * @return void
	 */
	public function testOrganisationsReturnsValidInfo() {
		$model = new OrganisationsModel();
		$organisations = $model->organisations();
		foreach ($organisations as $org) {
			$this->assertTrue(
				array_key_exists('name', $org),
				"Organisation without a name."
			);
			$this->assertTrue(
				array_key_exists('url', $org),
				"Organisation \"{$org['name']}\" has no URL."
			);
			$url_parsed = parse_url($org['url']);
			$this->assertTrue(
				($url_parsed !== false),
				"Failed to parse URL for organisation \"{$org['name']}\""
			);
			foreach (array('scheme', 'host', 'path') as $part) {
				$this->assertTrue(
					array_key_exists($part, $url_parsed),
					"Missing $part for organisation \"{$org['name']}\""
				);
			}
		}
	}
}

?>
