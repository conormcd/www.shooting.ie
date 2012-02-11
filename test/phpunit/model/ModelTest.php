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
 * Test the Model class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ModelTest extends TestCase {
	/**
	 * Fake out the APC methods we use.
	 *
	 * @return void
	 */
	public function setUp() {
		if (!function_exists('apc_fetch')) {
			/**
			 * Fake implementation of apc_fetch.
			 *
			 * @param string  $key      Ignored.
			 * @param boolean &$success Return param, always false.
			 *
			 * @return mixed Always false.
			 */
			function apc_fetch($key, &$success) {
				$key = null;
				$success = false;
				return false;
			}
		}
		if (!function_exists('apc_store')) {
			/**
			 * Fake implementation of apc_store.
			 *
			 * @param string  $key Ignored.
			 * @param mixed   $var Ignored.
			 * @param integer $ttl Ignored.
			 *
			 * @return void
			 */
			function apc_store($key, $var, $ttl) {
				// Do nothing
				$key = null;
				$var = null;
				$ttl = null;
			}
		}
	}

	/**
	 * Ensure that the Model::_load() method does the right thing.
	 *
	 * @return void
	 */
	public function testDataPropagates() {
		$test_data = array(
			false,
			true,
			null,
			0,
			-1,
			1,
			0.1,
			'Test',
			array(),
			array(0, 1, 2),
			array('foo' => 'bar'),
		);
		foreach ($test_data as $test) {
			$data_as_string = var_export($test, true);
			$testmodel = new TestModelImpl($test);
			$this->assertSame(
				$test,
				$testmodel->getData(),
				"$data_as_string did not propagate."
			);
			$testmodel = new TestCachingModelImpl($test);
			$this->assertSame(
				$test,
				$testmodel->getData(),
				"$data_as_string did not propagate (cold cache)."
			);
			$this->assertSame(
				$test,
				$testmodel->getData(),
				"$data_as_string did not propagate (warm cache)."
			);
		}
	}
}

/**
 * A simple implementation of Model, just to test the triggering of the _load() 
 * method.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestModelImpl extends Model {
	/** Fake backing data. */
	protected $_test_input;

	/**
	 * Init with fake backing data.
	 *
	 * @param mixed $test_input The data that should be "loaded" by _load().
	 */
	public function __construct($test_input) {
		$this->_test_input = $test_input;
		parent::__construct();
	}

	/**
	 * Access method so that we can check on the result of Model::_load().
	 *
	 * @return mixed Whatever data was loaded in with _load()
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Implement _load() from Model. 
	 *
	 * @return mixed The data "loaded" from the constructor.
	 */
	protected function _load() {
		return $this->_test_input;
	}
}

/**
 * A caching version of TestModelImpl.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestCachingModelImpl extends TestModelImpl {
	/**
	 * Load the data from a cache if possible.
	 *
	 * @return mixed The same as from TestModelImpl::_load()
	 */
	protected function _load() {
		$this->_data = $this->_cache->get('data');
		if ($this->_data === null) {
			$this->_data = parent::_load();
			$this->_cache->set('data', $this->_data, 0);
		}
		return $this->_data;
	}
}

?>
