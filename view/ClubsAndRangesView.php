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
 * View for /clubs-and-ranges.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRangesView extends View {
	/** Make the clubs and ranges tab active. */
	public $activeTabClubsAndRanges = "active";

	/** Include the Google Maps JS. */
	public $additionalExternalScripts = array(
		'src' => 'http://maps.googleapis.com/maps/api/js?sensor=false'
	);

	/**
	 * Proxy for the clubs method in ClubsAndRangesModel.
	 *
	 * @return array See ClubsAndRangesModel::clubs() for details.
	 */
	public function clubs() {
		return $this->_model->clubs();
	}

	/**
	 * Include some stuff as inline JavaScript.
	 *
	 * @return string Some valid JavaScript to be included on the page.
	 */
	public function inlineScript() {
		$javascript = "var map = {};\nvar clubs_and_ranges = %s\n";
		$clubs = array();
		foreach ($this->clubs() as $club) {
			$clubs[$club['id']] = $club;
		}
		return sprintf($javascript, json_encode($clubs));
	}
}

?>
