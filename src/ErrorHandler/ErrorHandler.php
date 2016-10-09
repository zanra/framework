<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\ErrorHandler;

use Zanra\Framework\ErrorHandler\ErrorHandlerWrapperInterface;

/**
 * Zanra ErrorHandler
 *
 * @author Targalis
 *
 */
class ErrorHandler
{
    /**
     * @var ErrorHandlerWrapperInterface
     */
    private static $wrapper;

    /**
     * Initialize errors wrapping
     *
     * @param ErrorHandlerWrapperInterface $wrapper
     */
    public static function init(ErrorHandlerWrapperInterface $wrapper)
    {
        ob_start();

        self::$wrapper = $wrapper;

        $error_handler = function($errno, $errstr, $errfile, $errline) {
            // Ignore it if they're not in error_reporting
            if (!(error_reporting() & $errno)) {
                return;
            }

            try {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                if (ob_get_length()) {
                    ob_end_clean();
                }

                self::$wrapper->wrap($e, null);
            }
        };

        $exception_handler = function($e) {
            self::$wrapper->wrap($e, null);
        };

        $fatal_handler = function() use ($error_handler) {
            $error = error_get_last();
            if (in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE))) {
                $error_handler($error['type'], $error['message'], $error['file'], $error['line']);
            }
        };

        // Error handler
        set_error_handler($error_handler);

        // Exception handler
        set_exception_handler($exception_handler);

        // Fatal error handler
        register_shutdown_function($fatal_handler);
    }
}