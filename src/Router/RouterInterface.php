<?php
namespace Zanra\Framework\Router;

interface RouterInterface
{
  public function matchRequest();
  
  public function generateContextUrl($routename, array $parameters = array());
}
