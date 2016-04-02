<?php

use Zanra\Framework\UrlBag\UrlBag;
use Zanra\Framework\Router\Router;
use Zanra\Framework\FileLoader\FileLoader;

class RouterTest extends PHPUnit_Framework_TestCase {
 
  public function testIfIsMobile()
  {
		$urlBag = new UrlBag('/home/10/article/20');
		$fileLoader = FileLoader::getInstance();
		
		$routes = $fileLoader->load(__DIR__.'/config/routes.ini');
		$router = new Router($routes, $urlBag);
		
		$routeBag = $router->matchRequest();
		
    $this->assertTrue(true);
  }
}