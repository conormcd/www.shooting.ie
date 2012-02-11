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
 * Test the JSONModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class JSONModelTest extends TestCase {
	/**
	 * Test JSONModel::_loadJSONDirectory()
	 *
	 * @return void
	 */
	public function testLoadJSONDirectory() {
		$model = new ExposedJSONModel();
		$data = $model->loadJSONDirectory(ROOT . '/data/organisations');
		$this->assertTrue(
			count($data) > 0,
			"Empty array returned by _loadJSONDirectory"
		);
		$data = $model->loadJSONDirectory(ROOT . '/data/does-not-exist');
		$this->assertSame(
			array(),
			$data,
			"Empty array returned by _loadJSONDirectory"
		);
	}
	
	/**
	 * Test JSONModel::_readJSON()
	 *
	 * @return void
	 */
	public function testReadJSON() {
		$model = new ExposedJSONModel();
		$data = $model->readJSON(ROOT . '/data/organisations/ntsa.json');
		$this->assertTrue(
			count($data) > 0,
			"Empty array returned by _readJSON"
		);
		try {
			$data = $model->readJSON(ROOT . '/data/organisations/_.json');
			$exception = false;
		} catch (Exception $e) {
			$exception = true;
		}
		$this->assertTrue($exception, "_readJSON failed to throw.");
	}

	/**
	 * Test JSONModel::_parseJSON().
	 *
	 * @return void
	 */
	public function testParseJSON() {
		$model = new ExposedJSONModel();

		// Success expected
		$this->assertSame(
			array('foo' => 'bar'),
			$model->parseJSON('{"foo": "bar"}'),
			"Simple associative array failed to decode."
		);

		// Stack depth exception
		try {
			$json = '';
			for ($i = 0; $i < 1000; $i++) {
				$json .= '{"foo":';
			}
			$json .= '"bar"';
			for ($i = 0; $i < 1000; $i++) {
				$json .= '}';
			}
			$model->parseJSON($json);
			$exception = false;
		} catch (Exception $e) {
			$this->assertSame(
				JSON_ERROR_DEPTH,
				$e->getCode(),
				"Wrong json_last_error() code."
			);
			$exception = true;
		}
		$this->assertTrue($exception, "Depth test parsed successfully.");

		// State mismatch
		try {
			$model->parseJSON('{"foo":["bar"}}');
			$exception = false;
		} catch (Exception $e) {
			$this->assertSame(
				JSON_ERROR_STATE_MISMATCH,
				$e->getCode(),
				"Wrong json_last_error() code."
			);
			$exception = true;
		}
		$this->assertTrue($exception, "State test parsed successfully.");

		// Unexpected control character
		try {
			$model->parseJSON("\007");
			$exception = false;
		} catch (Exception $e) {
			$this->assertSame(
				JSON_ERROR_CTRL_CHAR,
				$e->getCode(),
				"Wrong json_last_error() code."
			);
			$exception = true;
		}
		$this->assertTrue($exception, "Bell character parsed successfully.");

		// Bad syntax
		try {
			$model->parseJSON("{");
			$exception = false;
		} catch (Exception $e) {
			$this->assertSame(
				JSON_ERROR_SYNTAX,
				$e->getCode(),
				"Wrong json_last_error() code."
			);
			$exception = true;
		}
		$this->assertTrue($exception, "Single curly parsed successfully.");
		
		// UTF-8 error
		try {
			$thorn = mb_decode_numericentity(
				"&#254;",
				array(0x0, 0xffff, 0, 0xffff),
				"ISO-8859-1"
			);
			$model->parseJSON('{"helgi": "' . $thorn . 'ormar"}');
			$exception = false;
		} catch (Exception $e) {
			$this->assertSame(
				JSON_ERROR_UTF8,
				$e->getCode(),
				"Wrong json_last_error() code."
			);
			$exception = true;
		}
		$this->assertTrue($exception, "Mangled UTF-8 parsed successfully.");
	}

	/**
	 * Test JSONModel::_getFiles()
	 *
	 * @return void
	 */
	public function testGetFiles() {
		$model = new ExposedJSONModel();
		$files = $model->getFiles(ROOT . '/data/organisations');
		$this->assertTrue(
			count($files) > 0,
			"Organisations data directory is empty"
		);
		$files = $model->getFiles(ROOT . '/data/does-not-exist');
		$this->assertSame(
			array(),
			$files,
			"/data/does-not-exist is apparently not empty"
		);
	}
}

/**
 * A wrapper around JSONModel in order to expose its protected methods.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ExposedJSONModel extends JSONModel {
	/**
	 * Expose JSONModel::_loadJSONDirectory()
	 *
	 * @param string $dir See JSONModel::_loadJSONDirectory() for details.
	 *
	 * @return array See JSONModel::_loadJSONDirectory() for details.
	 */
	public function loadJSONDirectory($dir) {
		return $this->_loadJSONDirectory($dir);
	}

	/**
	 * Expose JSONModel::_readJSON()
	 *
	 * @param string $file See JSONModel::_readJSON() for details.
	 *
	 * @return array See JSONModel::_readJSON() for details.
	 */
	public function readJSON($file) {
		return $this->_readJSON($file);
	}

	/**
	 * Expose JSONModel::_parseJSON()
	 *
	 * @param string $str See JSONModel::_parseJSON() for details.
	 *
	 * @return array See JSONModel::_parseJSON() for details.
	 */
	public function parseJSON($str) {
		return $this->_parseJSON($str);
	}

	/**
	 * Expose JSONModel::_getFiles()
	 *
	 * @param string $dir     See JSONModel::_getFiles() for details.
	 * @param string $pattern See JSONModel::_getFiles() for details.
	 *
	 * @return array See JSONModel::_getFiles() for details.
	 */
	public function getFiles($dir, $pattern = null) {
		return $this->_getFiles($dir, $pattern);
	}

	/**
	 * Dummy implementation of Model::_load() just to get this working.
	 *
	 * @return void
	 */
	protected function _load() {
		$this->_data = array();
	}
}

?>
