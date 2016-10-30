<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zanra\Framework\Router\Router;
use Zanra\Framework\UrlBag\UrlBag;
use Zanra\Framework\FileLoader\FileLoader;

/**
 * RouterTest
 *
 * @author Targalis
 *
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $fileLoader;
    
    protected $routes;

    protected function setUp()
    {
        $this->fileLoader = FileLoader::getInstance();
    }

    public function testMatchRequest()
    {
        $urlBag = new UrlBag();
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/routes.ini"); 
        $router = new Router($routes);

        $this->assertInternalType('array', $router->matchRequest($urlBag));
    }

    public function testMatchRequestFalse()
    {
        $urlBag = new UrlBag();
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/no_pattern.ini"); 
        $router = new Router($routes);

        $this->assertFalse($router->matchRequest($urlBag));
    }

    /**
     * @expectedException Zanra\Framework\Router\Exception\RouteNotFoundException
     */
    public function testRouteNotFoundException()
    {
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/routes.ini"); 
        $router = new Router($routes);
        $url = $router->generateUrl("routeNotFound", array());
    }

    /**
     * @expectedException Zanra\Framework\Router\Exception\InvalidParameterException
     */
    public function testInvalidParameterException()
    {
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/routes.ini"); 
        $router = new Router($routes);
        $params = array('undefinedKey' => 'val');
        $url = $router->generateUrl("home", $params);
    }

    public function testGenerateUrl()
    {
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/routes.ini"); 
        $router = new Router($routes);
        $url = $router->generateUrl("home", array());

        $this->assertTrue(is_string($url));
    }
}
