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
 * Ensure that all the JSON files contain well-formed JSON.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class JSONTest extends TestCase {
	/**
	 * Check that all the JSON files under the data directory are readable and 
	 * contain well-formed JSON.
	 *
	 * @return void
	 */
	public function testAllJSONFilesAreWellFormed() {
		foreach ($this->_findJSONFiles(ROOT . '/data') as $file) {
			$contents = file_get_contents($file);
			$this->assertFalse(($contents === false), "Failed to read $file.");
			$array = json_decode($contents, true);
			$this->assertNotNull($array, "$file is malformed JSON.");
		}
	}

	/**
	 * Find all the files matching *.json in a given directory.
	 *
	 * @param string $directory The directory to search for JSON files.
	 *
	 * @return array The full paths to all the .json files.
	 */
	private function _findJSONFiles($directory) {
		$files = array();
		if ($handle = opendir($directory)) {
			while (($file = readdir($handle)) !== false) {
				$path = "$directory/$file";
				if (is_file($path) && preg_match("/\.json$/", $path)) {
					$files[] = $path;
				} else if (is_dir($path) && $file[0] !== '.') {
					$files = array_merge($files, $this->_findJSONFiles($path));
				}
			}
			closedir($handle);
		}
		return $files;
	}
}

?>
