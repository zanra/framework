<?php
namespace Zanra\Framework\FileLoader\Exception;

class FileNotFoundException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
