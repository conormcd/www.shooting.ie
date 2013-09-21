<?php

require_once __DIR__ . '/Environment.php';

/**
 * Handler for errors and exceptions. This is just a facade to decouple the 
 * exception tracker from the rest of the code.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorHandler {
    /** The Sentry exception handler client. */
    private static $sentry = null;

    /**
     * Load the exception handler and set the appropriate global error 
     * handlers.
     *
     * @return void
     */
    public static function init() {
        if ($_ENV['SENTRY_DSN']) {
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
     *
     * @return void
     */
    public static function handleException($exception) {
        if (self::$sentry) {
            self::$sentry->captureException($exception);
        } else {
            throw $exception;
        }
    }
}

// Merely including this file is grounds for activating it.
ErrorHandler::init();

?>
