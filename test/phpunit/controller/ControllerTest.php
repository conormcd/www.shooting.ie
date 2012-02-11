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
require_once ROOT . '/lib/klein/klein.php';

/**
 * Test the Controller class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ControllerTest extends TestCase {
	/** The actions to test with the controller. */
	private static $_test_actions = array(
		'about',
		// 'calendar', // Too slow - we'll test it once, directly.
		'clubs-and-ranges',
		'help',
		'organisations',
	);

	/** 
	 * Make sure that a GET request to any of the test actions produces 
	 * parseable HTML.
	 *
	 * @return void
	 */
	public function testGetProducesParseableHTML() {
		$req = new _Request();
		$res = new _Response();
		foreach (self::$_test_actions as $action) {
			$controller = new Controller($action, $req, $res);
			ob_start();
			$controller->get();
			$output = ob_get_clean();

			try {
				$dom = new DOMDocument();
				$dom->loadHTML($output);
			} catch (Exception $e) {
				$this->fail("/$action is unparseable: " . $e->getMessage());
			}
			$this->assertTrue(true, "/$action is parseable.");
		}
	}

	/**
	 * Ensure that the controller can get a non-null model which is either the 
	 * klein response object or a custom model class.
	 *
	 * @return void
	 */
	public function testGetModelReturnsAValidObject() {
		$req = new _Request();
		$res = new _Response();
		foreach (self::$_test_actions as $action) {
			$controller = new ExposedController($action, $req, $res);
			$model = $controller->getModel();
			
			// Make sure it's not null
			$this->assertNotNull(
				$model,
				"Controller for \"$action\" returned a null model."
			);

			// Make sure it's a valid object.
			if (get_class($model) !== '_Response') {
				$this->assertInstanceOf(
					'Model',
					$model,
					"The model for \"$action\" is not a Model object."
				);
			}
		}
	}

	/** 
	 * Make sure that at least the basic, required templates are returned for 
	 * each of the actions.
	 *
	 * @return void
	 */
	public function testGetTemplatesReturnsRequiredTemplates() {
		$req = new _Request();
		$res = new _Response();
		foreach (self::$_test_actions as $action) {
			$controller = new ExposedController($action, $req, $res);
			$templates = $controller->getTemplates();
			foreach (array('main', 'head', 'tail') as $template) {
				$this->assertTrue(
					array_key_exists($template, $templates),
					"Missing \"$template\" template for /$action"
				);
			}
		}
	}

	/**
	 * Ensure that the controller can get a non-null view which is either the 
	 * klein response object or a custom view class.
	 *
	 * @return void
	 */
	public function testGetViewReturnsAValidObject() {
		$req = new _Request();
		$res = new _Response();
		foreach (self::$_test_actions as $action) {
			$controller = new ExposedController($action, $req, $res);
			$view = $controller->getView();
			
			// Make sure it's not null
			$this->assertNotNull(
				$view,
				"Controller for \"$action\" returned a null view."
			);

			// Make sure it's a valid object.
			if (get_class($view) !== '_Response') {
				$this->assertInstanceOf(
					'View',
					$view,
					"The view for \"$action\" is not a View object."
				);
			}
		}
	}

	/**
	 * Make sure that the controller constructor validates its args. 
	 *
	 * @return void
	 */
	public function testConstructorValidatesArgs() {
		$req = new _Request();
		$res = new _Response();
		$failures = array(
			array(null, null, null),
			array(null, null, $res),
			array(null, $req, null),
			array(null, $req, $res),
			array('about', null, null),
			array('about', null, $res),
			array('about', $req, null),
			array('', null, null),
		);
		foreach ($failures as $args) {
			try {
				$c = new Controller($args[0], $args[1], $args[2]);
				$this->assertNotNull($c, "Controller constructor borked.");
				$exception = false;
			} catch (Exception $e) {
				$exception = true;
			}
			$this->assertTrue(
				$exception,
				var_export($args, true) . ' failed to cause an exception'
			);
		}
	}
}

/**
 * A wrapper for Controller which allows public access to some protected 
 * members.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ExposedController extends Controller {
	/**
	 * Expose Controller::_getModel.
	 *
	 * @return See Controller::_getModel.
	 */
	public function getModel() {
		return $this->_getModel();
	}

	/**
	 * Expose Controller::_getTemplates.
	 *
	 * @return See Controller::_getTemplates.
	 */
	public function getTemplates() {
		return $this->_getTemplates();
	}

	/**
	 * Expose Controller::_getView.
	 *
	 * @return See Controller::_getView.
	 */
	public function getView() {
		return $this->_getView();
	}
}

?>
