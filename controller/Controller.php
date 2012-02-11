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

require ROOT . '/lib/mustache/Mustache.php';

/**
 * A default controller which can be used for most GET requests. This can be
 * extended in order to provide more specific handling of requests. Simply
 * extend this class and then modify the routing in public/index.php in order
 * to direct requests to your new controller.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Controller {
	/** Calendar for /calendar, ClubsAndRanges for /clubs-and-ranges, etc. */
	protected $_action_name;

	/** The klein _Request object for the current page request. */
	protected $_request;

	/** The klein _Response object for the current page request. */
	protected $_response;

	/** A cache for _getModel(). */
	protected $_model;

	/** A cache for _getView(). */
	protected $_view;

	/**
	 * Initialise this controller. 
	 *
	 * @param string $action   The first portion of the URL after the domain.
	 * @param object $request  The _Request object from klein.
	 * @param object $response The _Response object from klein.
	 */
	public function __construct($action, $request, $response) {
		if (!$action) {
			throw new Exception("Blank action provided.");
		}
		if (!($request instanceof _Request)) {
			throw new Exception("Bad request object provided.");
		}
		if (!($response instanceof _Response)) {
			throw new Exception("Bad response object provided.");
		}

		$this->_action = $action;
		$this->_request = $request;
		$this->_response = $response;

		$this->_action_name = '';
		foreach (explode('-', $action) as $part) {
			$this->_action_name .= ucfirst($part);
		}
	}

	/**
	 * Handle a GET request.
	 *
	 * @return void
	 */
	public function get() {
		$templates = $this->_getTemplates();
		$mustache = new Mustache(
			$templates['main'],
			$this->_getView(),
			$templates
		);
		print $mustache->render();
	}

	/**
	 * Get the model for the current request. 
	 *
	 * @return object An appropriate sub-class of Model if one exists, if not,
	 *                the _Response object from klein is used as the model.
	 */
	protected function _getModel() {
		if (!$this->_model) {
			$model_name = $this->_action_name . 'Model';
			$model_file = ROOT . "/model/$model_name.php";
			if (file_exists($model_file)) {
				$this->_model = new $model_name();
			} else {
				$this->_model = $this->_response;
			}
		}
		return $this->_model;
	}

	/**
	 * Get the templates for the current page request.
	 *
	 * @param string $template_dir ONLY FOR RECURSIVE CALLS. DO NOT USE.
	 *
	 * @return array The templates for the current page request.
	 */
	protected function _getTemplates($template_dir = null) {
		// By default, this returns the action-specific templates and the root
		// templates.
		if ($template_dir === null) {
			return array_merge(
				$this->_getTemplates(ROOT . '/template'),
				$this->_getTemplates(ROOT . '/template/' . $this->_action)
			);
		}

		// If we make it to here, we're loading a specific directory
		$templates = array();
		if ($dir = opendir($template_dir)) {
			while (($filename = readdir($dir)) !== false) {
				if ($filename[0] != '.' && preg_match("/\.html$/", $filename)) {
					$name = preg_replace('/\.html$/', '', $filename);
					$value = file_get_contents($template_dir . '/' . $filename);
					$templates[$name] = $value;
				}
			}
			closedir($dir);
		} else {
			throw new Exception('Missing template directory: ' . $template_dir);
		}

		return $templates;
	}

	/**
	 * Get the view for the current request. 
	 *
	 * @return object An appropriate sub-class of View if one exists, if not, 
	 *                the _Response object from klein is used as the view.
	 */
	protected function _getView() {
		if (!$this->_view) {
			$view_name = $this->_action_name . 'View';
			$view_file = ROOT . "/view/$view_name.php";
			if (file_exists($view_file)) {
				$this->_view = new $view_name($this->_getModel());
			} else {
				$this->_view = $this->_response;
			}
		}
		return $this->_view;
	}
}

?>
