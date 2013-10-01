<?php

require_once __DIR__ . '/global.php';

/**
 * Handler for errors and exceptions. This is just a facade to decouple the
 * exception tracker from the rest of the code.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorHandler {
    /** The Sentry exception handler client. */
    public static $sentry = null;

    /**
     * Load the exception handler and set the appropriate global error
     * handlers.
     *
     * @return void
     */
    public static function init() {
        if (array_key_exists('SENTRY_DSN', $_ENV)) {
            include_once __DIR__ . '/raven-php/lib/Raven/Autoloader.php';
            Raven_Autoloader::register();
            self::$sentry = new Raven_Client($_ENV['SENTRY_DSN']);
            $error_handler = new Raven_ErrorHandler(self::$sentry);
            set_error_handler(array($error_handler, 'handleError'));
            set_exception_handler(array($error_handler, 'handleException'));
        }
    }

    /**
     * Handle an exception via the exception handler or re-throw it if there's
     * no handler installed.
     *
     * @param Exception $exception The exception to handle.
     * @param boolean   $re_throw  Re-throw the exception after handling it. By
     *                             default the exception is not rethrown if
     *                             there's a working Sentry connection.
     *
     * @return void
     */
    public static function handleException($exception, $re_throw = null) {
        if (self::$sentry) {
            if ($re_throw === null) {
                $re_throw = false;
            }
            self::$sentry->captureException($exception);
        } else {
            if ($re_throw === null) {
                $re_throw = true;
            }
        }
        if ($re_throw) {
            throw $exception;
        }
    }
}

// Merely including this file is grounds for activating it.
ErrorHandler::init();

?>
