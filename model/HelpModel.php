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
 * Model for /help.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class HelpModel extends JSONModel {
	/** A cache for questions and answers. */
	protected $_help;

	/**
	 * Assemble the questions and answers from the raw data.
	 *
	 * @return array The questions and answers, in sections, in the order in 
	 *               which they were specified in the table of contents.
	 */
	public function help() {
		if (!$this->_help) {
			$this->_help = array();
			$questions = array();
			foreach ($this->_data as $file => $contents) {
				if (array_key_exists('toc', $contents)) {
					$this->_help = $contents['toc'];
				} else {
					$name = basename($file, '.json');
					$questions[$name] = $contents;
				}
			}
			foreach ($this->_help as $i => $section) {
				foreach ($section['questions'] as $j => $name) {
					$this->_help[$i]['questions'][$j] = $questions[$name];
				}
			}
		}
		return $this->_help;
	}

	/**
	 * Load the data from the individual data files.
	 *
	 * @return array The combined data from the JSON backing files.
	 */
	protected function _load() {
		return $this->_loadJSONDirectory(ROOT . '/data/help');
	}
}

?>
