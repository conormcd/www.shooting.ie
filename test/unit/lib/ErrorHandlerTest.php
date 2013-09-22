<?php

require_once __DIR__ . '/../../../lib/ErrorHandler.php';

/**
 * Test the ErrorHandler class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorHandlerTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Test the handleException method with no sentry and default re-throw.
     *
     * @return void
     */
    public function testHandleExceptionNoSentry() {
        $this->assertNull(ErrorHandler::$sentry);
        $this->setExpectedException('Exception');
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e);
        }
    }

    /**
     * Test the handleException method with no sentry and forced re-throws.
     *
     * @return void
     */
    public function testHandleExceptionNoSentryForceReThrow() {
        $this->assertNull(ErrorHandler::$sentry);
        $this->setExpectedException('Exception');
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e, true);
        }
    }

    /**
     * Test the handleException method with no sentry and no re-throws.
     *
     * @return void
     */
    public function testHandleExceptionNoSentryNoReThrow() {
        $this->assertNull(ErrorHandler::$sentry);
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e, false);
        }
    }

    /**
     * Test the handleException method with sentry and default re-throw.
     *
     * @return void
     */
    public function testHandleExceptionSentry() {
        ErrorHandler::$sentry = new SentryFake();
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e);
            $this->assertEquals($e, ErrorHandler::$sentry->exception);
        }
        ErrorHandler::$sentry = null;
    }

    /**
     * Test the handleException method with no sentry and forced re-throws.
     *
     * @return void
     */
    public function testHandleExceptionSentryForceReThrow() {
        ErrorHandler::$sentry = new SentryFake();
        $this->setExpectedException('Exception');
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e, true);
            $this->assertEquals($e, ErrorHandler::$sentry->exception);
        }
        ErrorHandler::$sentry = null;
    }

    /**
     * Test the handleException method with no sentry and no re-throws.
     *
     * @return void
     */
    public function testHandleExceptionSentryNoReThrow() {
        ErrorHandler::$sentry = new SentryFake();
        try {
            throw new Exception('Test Exception');
        } catch (Exception $e) {
            ErrorHandler::handleException($e, false);
            $this->assertEquals($e, ErrorHandler::$sentry->exception);
        }
        ErrorHandler::$sentry = null;
    }
}

/**
 * A fake to use instead of the live Raven_Client class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class SentryFake {
    /** The last exception captured. */
    public $exception;

    /**
     * Handle an exception.
     *
     * @param Exception $exception The exception to be handled.
     *
     * @return void
     */
    public function captureException($exception) {
        $this->exception = $exception;
    }
}

?>
