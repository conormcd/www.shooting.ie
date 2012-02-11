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
 * A base model where the source of the data is encoded in JSON.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class JSONModel extends Model {
	/**
	 * Load the data from a directory containing .json files.
	 *
	 * @param string $dir The path to a directory containing JSON files.
	 *
	 * @return array An associative array where the keys are the paths to the 
	 *               JSON files and the values are the decoded JSON objects 
	 *               contained within them.
	 */
	protected function _loadJSONDirectory($dir) {
		$data = array();
		foreach ($this->_getFiles($dir, '/^\w.*\.json/') as $file) {
			$data[$file] = $this->_readJSON($file);
		}
		ksort($data);
		return $data;
	}

	/**
	 * Read and decode JSON from a file or URL.
	 *
	 * @param string $file A file or URL from which to read JSON.
	 *
	 * @return array The JSON in the file, decoded into an array.
	 */
	protected function _readJSON($file) {
		$json = false;
		if (file_exists($file) || preg_match('/^http/', $file)) {
			$json = file_get_contents($file);
		}
		if ($json === false) {
			throw new Exception("Failed to read $file");
		}
		return $this->_parseJSON($json);
	}

	/**
	 * Parse a JSON string.
	 *
	 * @param string $str A string which should contain valid JSON.
	 *
	 * @return array The JSON in the string, decoded into an array.
	 */
	protected function _parseJSON($str) {
		$data = json_decode($str, true);
		if ($data === null) {
			$json_error = json_last_error();
			$message  = "Malformed JSON";
			switch ($json_error) {
				case JSON_ERROR_DEPTH:
					$message .= ": Maximum stack depth exceeded.";
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$message .= ": State mismatch.";
					break;
				case JSON_ERROR_CTRL_CHAR:
					$message .= ": Unexpected control character found.";
					break;
				case JSON_ERROR_SYNTAX:
					$message .= ": Syntax error.";
					break;
				case JSON_ERROR_UTF8:
					$message .= ": Malformed UTF-8 character.";
					break;
			}
			throw new Exception($message, $json_error);
		}
		return $data;
	}

	/**
	 * Get a list of the full paths of files in a given directory.
	 *
	 * @param string $dir     The path to a directory.
	 * @param string $pattern A regular expression which the base name of the 
	 *                        file must match if it is to be returned.
	 *
	 * @return array All the files matching $pattern in $dir.
	 */
	protected function _getFiles($dir, $pattern = null) {
		$files = array();
		if (is_dir($dir)) {
			if ($handle = opendir($dir)) {
				while (($file = readdir($handle)) !== false) {
					if ($pattern === null || preg_match($pattern, $file)) {
						$files[] = "$dir/$file";
					}
				}
				closedir($handle);
			}
		}
		return $files;
	}
}

?>
