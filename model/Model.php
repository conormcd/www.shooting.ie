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
 * A base model.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Model {
	/** A cache for expensive data. */
	protected $_cache;

	/** The raw data loaded into the model. */
	protected $_data;

	/** Load the model. */
	public function __construct() {
		$this->_cache = new APCModelCache();
		$this->_data = $this->_load();
	}

	/**
	 * Load the data. 
	 *
	 * @return array   The data which should end up in $this->_data.
	 */
	protected abstract function _load();
}

/**
 * A cache implementation which uses APC as the backing store.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class APCModelCache {
	/**
	 * Retrieve an object from the cache.
	 *
	 * @param string $key The key referring to the object to retrieve.
	 *
	 * @return mixed The value pointed to by $key or null if it can't be found.
	 */
	public function get($key) {
		if (!function_exists('apc_fetch')) {
			return null;
		}
		$value = apc_fetch($this->_key($key), $success);
		return $success ? $value : null;
	}

	/**
	 * Insert an item into the cache.
	 *
	 * @param string $key   The key by which you will retrieve the value.
	 * @param mixed  $value The value to store.
	 * @param int    $ttl   The number of seconds the value should persist for.
	 *
	 * @return void
	 */
	public function set($key, $value, $ttl) {
		if (function_exists('apc_store')) {
			apc_store($this->_key($key), $value, $ttl);
		}
	}

	/**
	 * Manipulate the key to prevent clashes with other users of APC.
	 *
	 * @param string $key The raw key to manipulate.
	 *
	 * @return string The key to use in calls to APC.
	 */
	private function _key($key) {
		return "www.shooting.ie-$key";
	}
}

?>
