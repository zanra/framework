<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
use Zanra\Framework\ErrorHandler\ErrorHandlerWrapperInterface;
use Zanra\Framework\Application\Application;

/**
 * Zanra ErrorWrapperTest
 *
 * @author Targalis
 *
 */
class ErrorWrapperTest implements ErrorHandlerWrapperInterface
{
    /**
     * final private function __clone();          // Inhibe le clonage des exceptions.
     *
     * final public function getMessage();        // message de l'exception
     * final public function getCode();           // code de l'exception
     * final public function getFile();           // nom du fichier source
     * final public function getLine();           // ligne du fichier source
     * final public function getTrace();          // un tableau de backtrace()
     * final public function getPrevious();       // exception précédente (depuis PHP 5.3)
     * final public function getTraceAsString();  // chaîne formatée de trace
     *
     * @param Exception $exception
     * @param integer $type
    */
    public function wrap($exception, $type = null)
    {
        $application = Application::getInstance();
    }
}
