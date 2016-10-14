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
        $global_handler = function($type, $errno, $code, $errstr, $errfile, $errline) use ($wrapper, $logsDir){
            try {
                throw new \ErrorException($errstr, $code, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                if (ob_get_length()) {
                    ob_end_clean();
                }
                
                if ($type === self::EXCEPTION) {
                    $wrapper->wrap($e, $type);
                } else {
                    die($e->getMessage());
                }

                if (null !== $logsDir) {
                    if (!is_dir($logsDir)) {
                        throw new ErrorLogsDirectoryNotFoundException(
                            sprintf('Error logs directory "%s" not found', $logsDir));
                    }
                    
                    error_log($e->getMessage(), 3, $logsDir. '/error.log');
                }
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
        $fatal_handler = function() use ($global_handler) {
            $error = error_get_last();
            if (in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE))) {
                $global_handler(self::FATAL_ERROR_EXCEPTION, $error['type'], 0, $error['message'], $error['file'], $error['line']);
            }
        };

        set_exception_handler($exception_handler);
        set_error_handler($error_handler);
        register_shutdown_function($fatal_handler);
    }
}