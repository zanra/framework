<?php
namespace Zanra\Framework\ErrorHandler;

use Zanra\Framework\ErrorHandler\ErrorHandlerWrapperInterface;

class ErrorHandler
{
    const EXCEPTION = 'exception';
    const ERROR_EXCEPTION = 'error';
    const FATAL_ERROR_EXCEPTION = 'fatal';
  
    private static $wrapper;
  
    public static function init(ErrorHandlerWrapperInterface $wrapper)
    {
        self::$wrapper = $wrapper;
    
        // Exception handler
        set_exception_handler(function($e) {
            if (ob_get_length()) 
                ob_end_clean();
      
            self::$wrapper->wrap($e, self::EXCEPTION);
            exit(0);
        });
    
        // Error handler
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            try {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            } catch (\Exception $e) {
                if (ob_get_length()) 
                    ob_end_clean();
        
                self::$wrapper->wrap($e, self::ERROR_EXCEPTION);
                exit(0);
            }
        });
    
        // Fatal error handler
        register_shutdown_function(function() {
            try {
                $error = error_get_last();
                if($error != null)
                    throw new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
            } catch (\Exception $e) {
                if (ob_get_length()) 
                    ob_end_clean();
        
                self::$wrapper->wrap($e, self::FATAL_ERROR_EXCEPTION);
                exit(0);
            }
        });
    }
}
