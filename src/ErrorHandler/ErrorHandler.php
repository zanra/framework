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
 */
class ErrorHandler
{
    const EXCEPTION = 'exception';
    const ERROR_EXCEPTION = 'error';
    const FATAL_ERROR_EXCEPTION = 'fatalError';

    private static function setHeaderStatusCode($code = 200)
    {
        $messages = array(
          // Informational 1xx
          100 => 'Continue',
          101 => 'Switching Protocols',

          // Success 2xx
          200 => 'OK',
          201 => 'Created',
          202 => 'Accepted',
          203 => 'Non-Authoritative Information',
          204 => 'No Content',
          205 => 'Reset Content',
          206 => 'Partial Content',

          // Redirection 3xx
          300 => 'Multiple Choices',
          301 => 'Moved Permanently',
          302 => 'Found',  // 1.1
          303 => 'See Other',
          304 => 'Not Modified',
          305 => 'Use Proxy',
          // 306 is deprecated but reserved
          307 => 'Temporary Redirect',

          // Client Error 4xx
          400 => 'Bad Request',
          401 => 'Unauthorized',
          402 => 'Payment Required',
          403 => 'Forbidden',
          404 => 'Not Found',
          405 => 'Method Not Allowed',
          406 => 'Not Acceptable',
          407 => 'Proxy Authentication Required',
          408 => 'Request Timeout',
          409 => 'Conflict',
          410 => 'Gone',
          411 => 'Length Required',
          412 => 'Precondition Failed',
          413 => 'Request Entity Too Large',
          414 => 'Request-URI Too Long',
          415 => 'Unsupported Media Type',
          416 => 'Requested Range Not Satisfiable',
          417 => 'Expectation Failed',

          // Server Error 5xx
          500 => 'Internal Server Error',
          501 => 'Not Implemented',
          502 => 'Bad Gateway',
          503 => 'Service Unavailable',
          504 => 'Gateway Timeout',
          505 => 'HTTP Version Not Supported',
          509 => 'Bandwidth Limit Exceeded'
        );

        // Default code if not Found is 500
        if (! in_array($code, array_keys($messages))) {
            $code = 500;
        }

        $statusMessage = $messages[$code];

        $serverProto = isset($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : null;

        if ($serverProto === null) {
            $serverProto = "HTTP/1.0";
        }

        $phpSapiName = strtolower(substr(php_sapi_name(), 0, 3));

        if ($phpSapiName == "cgi" || $phpSapiName == "fpm") {
            $serverProto = "Status: ";
        }

        header("{$serverProto} {$code} {$statusMessage}");

        return $code;
    }

    /**
     * Initialize errors wrapper
     *
     * @param string                       $logsDir
     * @param ErrorHandlerWrapperInterface $wrapper can only wrap exception. Errors and fatals are not wrapped
     */
    public static function init(ErrorHandlerWrapperInterface $wrapper, $logsDir = null)
    {
        // Exception render
        $exceptionRender = function ($type, $exception) use ($wrapper, $logsDir) {
            if (ob_get_length()) {
                ob_end_clean();
            }

            try {
                if (null !== $logsDir) {
                    if (! is_dir($logsDir)) {
                        throw new ErrorLogsDirectoryNotFoundException(
                            sprintf('Error logs directory "%s" not found', $logsDir)
                        );
                    }

                    $logsFile = date("Y-m-d"). '.log';
                    $errorLog = sprintf("[%s] %s", date("Y-m-d h:i:s"), $exception);
                    error_log($errorLog . '\n', 3, $logsDir . '/' . $logsFile);
                }
            } catch (\Exception $e) {
                $exception = $e;
            }

            $code = $exception->getCode();
            $code = ($code === 0) ? 500 : $code;

            self::setHeaderStatusCode($code);
            $wrapper->wrap($exception, $type);

            exit();
        };

        // Convert error and fatal to errorException
        $toErrorException = function ($type, $errno, $code, $errstr, $errfile, $errline) use ($exceptionRender) {
            try {
                throw new \ErrorException($errstr, $code, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                $exceptionRender($type, $e);
            }
        };

        // Exception handler
        $exceptionHandler = function ($exception) use ($exceptionRender) {
            $exceptionRender(self::EXCEPTION, $exception);
        };

        // Error handler
        $errorHandler = function ($errno, $errstr, $errfile, $errline) use ($toErrorException) {
            if (! (error_reporting() & $errno)) {
                return;
            }

            $toErrorException(self::ERROR_EXCEPTION, $errno, 0, $errstr, $errfile, $errline);
        };

        // Fatal error handler
        $fatalHandler = function () use ($toErrorException) {
            $error = error_get_last();
            if (in_array(
                $error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR,
                E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE)
            )) {
                $toErrorException(self::FATAL_ERROR_EXCEPTION, $error['type'], 0, $error['message'], $error['file'],
                $error['line']);
            }
        };

        set_exception_handler($exceptionHandler);
        set_error_handler($errorHandler);
        register_shutdown_function($fatalHandler);
    }
}
