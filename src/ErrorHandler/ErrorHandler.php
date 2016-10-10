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
    const EXCEPTION = 'exception';
    const ERROR_EXCEPTION = 'error';
    const FATAL_ERROR_EXCEPTION = 'fatalError';

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

        $global_handler = function($type, $errno, $code, $errstr, $errfile, $errline) {
            try {
                throw new \ErrorException($errstr, $code, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                if (ob_get_length()) {
                    ob_end_clean();
                }

                self::$wrapper->wrap($e, $type);
            }
        };

         // Exception handler
        $exception_handler = function($e) use ($global_handler) {
            $global_handler(self::EXCEPTION, E_ERROR, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        };

        // Error handler
        $error_handler = function($errno, $errstr, $errfile, $errline) use ($global_handler) {
            if (!(error_reporting() & $errno)) {
                return;
            }

            $global_handler(self::ERROR_EXCEPTION, $errno, 0, $errstr, $errfile, $errline);
        };

        // Fatal error handler
        $fatal_handler = function() use ($error_handler) {
            $error = error_get_last();
            if (in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE))) {
                $global_handler(self::FATAL_ERROR_EXCEPTION, $error['type'], 0, $error['message'], $error['file'], $error['line']);
            }
        };

        set_error_handler($error_handler);
        set_exception_handler($exception_handler);
        register_shutdown_function($fatal_handler);
    }
}