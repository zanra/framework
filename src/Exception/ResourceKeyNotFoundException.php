<?php
namespace Zanra\Framework\Exception;

class ResourceKeyNotFoundException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
