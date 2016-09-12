<?php
namespace Zanra\Framework\Router\Exception;

class InvalidParameterException extends \Exception
{
    public function __construct($message = null)
    {
        parent::__construct($message, 406);
    }
}
