<?php
namespace Zanra\Framework\FileLoader\Exception;

class WrongFileExtensionException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
