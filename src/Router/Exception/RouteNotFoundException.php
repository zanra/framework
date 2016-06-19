<?php
namespace Zanra\Framework\Router\Exception;

class RouteNotFoundException extends \ErrorException
{
  public function __construct($message = null)
  {
    parent::__construct($message, 404);
  }
}
