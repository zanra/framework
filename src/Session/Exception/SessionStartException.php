<?php
namespace Zanra\Framework\Session\Exception;

class SessionStartException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}       