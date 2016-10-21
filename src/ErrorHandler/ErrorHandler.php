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

use Zanra\Framework\ErrorHandler\Exception\ErrorLogsDirectoryNotFoundException;

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
     * Initialize errors wrapping
     *
     * @param string $logsDir
     * @param ErrorHandlerWrapperInterface $wrapper can only wrap exception. Errors and fatals are not wrapped
     */
    public static function init(ErrorHandlerWrapperInterface $wrapper, $logsDir = null)
    {
        // Exception render
        $exception_render = function($type, $exception) use ($wrapper, $logsDir) {
            if (ob_get_length()) {
                ob_end_clean();
            }

            if ($type === self::EXCEPTION) {
                $wrapper->wrap($exception, $type);
            } else {
                die($exception->getMessage());
            }

            if (null !== $logsDir) {
                if (!is_dir($logsDir)) {
                    throw new ErrorLogsDirectoryNotFoundException(
                        sprintf('Error logs directory "%s" not found', $logsDir));
                }

                error_log($exception->getMessage(), 3, $logsDir. '/error.log');
            }
        };

        // Convert error and fatal to errorException
        $toErrorException = function($type, $errno, $code, $errstr, $errfile, $errline) use ($render) {
            try {
                throw new \ErrorException($errstr, $code, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                $exception_render($type, $e);
            }
        };

        // Exception handler
        $exception_handler = function($e) use ($render) {
            $exception_render(self::EXCEPTION, $e);
        };

        // Error handler
        $error_handler = function($errno, $errstr, $errfile, $errline) use ($global_handler) {
            if (!(error_reporting() & $errno)) {
                return;
            }

            $toErrorException(self::ERROR_EXCEPTION, $errno, 0, $errstr, $errfile, $errline);
        };

        // Fatal error handler
        $fatal_handler = function() use ($global_handler) {
            $error = error_get_last();
            if (in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE))) {
                $toErrorException(self::FATAL_ERROR_EXCEPTION, $error['type'], 0, $error['message'], $error['file'], $error['line']);
            }
        };

        set_exception_handler($exception_handler);
        set_error_handler($error_handler);
        register_shutdown_function($fatal_handler);
    }
}