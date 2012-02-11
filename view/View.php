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
 * A base view class to be extended by all views.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class View extends Mustache {
	/** The model with which to render this view. */
	protected $_model;

	/** The templates for this view. */
	protected $_templates;

	/**
	 * Initialise the view with the model.
	 *
	 * @param string $action The action we're viewing for.
	 * @param object $model  The model to render with this view.
	 */
	public function __construct($action, $model) {
		$this->_templates = $this->_getTemplates($action);
		if (!array_key_exists('main', $this->_templates)) {
			$this->_templates['main'] = '{{> head}}{{> tail}}';
		}
		parent::__construct(
			$this->_templates['main'],
			null,
			$this->_templates
		);
		$this->_model = $model;
	}

	/**
	 * Get the templates for the current page request.
	 *
	 * @param string $action       The action we're viewing for.
	 * @param string $template_dir ONLY FOR RECURSIVE CALLS. DO NOT USE.
	 *
	 * @return array The templates for the current page request.
	 */
	protected function _getTemplates($action, $template_dir = null) {
		// By default, this returns the action-specific templates and the root
		// templates.
		if ($template_dir === null) {
			return array_merge(
				$this->_getTemplates($action, ROOT . '/template'),
				$this->_getTemplates($action, ROOT . '/template/' . $action)
			);
		}

		// If we make it to here, we're loading a specific directory
		$templates = array();
		if (file_exists($template_dir) && is_dir($template_dir)) {
			if ($dir = opendir($template_dir)) {
				while (($filename = readdir($dir)) !== false) {
					if ($filename[0] != '.' && preg_match("/\.html$/", $filename)) {
						$name = preg_replace('/\.html$/', '', $filename);
						$value = file_get_contents($template_dir . '/' . $filename);
						$templates[$name] = $value;
					}
				}
				closedir($dir);
			}
		}

		return $templates;
	}
}

?>
