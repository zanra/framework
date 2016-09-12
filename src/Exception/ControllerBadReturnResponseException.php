<?php
namespace Zanra\Framework\Exception;

class ControllerBadReturnResponseException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
