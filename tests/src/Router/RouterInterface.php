<?php
namespace Zanra\Framework\Router;

interface RouterInterface
{
  public function matchRequest();
  
  public function generateUrl($routename, array $parameters = array());
}
