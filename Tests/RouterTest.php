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
        $routes = $this->fileLoader->load(__DIR__ . "/../Tests/Mocks/routes.ini"); 
        $this->router = new Router($routes);
    }

    public function testMatchRequest()
    {
        $urlBag = new UrlBag("http://127.0.0.1");
        $this->assertInternalType('array', $this->router->matchRequest($urlBag));
    }

    public function testMatchRequestFalse()
    {
        $urlBag = new UrlBag("http://127.0.0.1/route/not/found/test/case");
        $this->assertFalse($this->router->matchRequest($urlBag));
    }

    /**
     * @expectedException Zanra\Framework\Router\Exception\RouteNotFoundException
     */
    public function testRouteNotFoundException()
    {
        $url = $this->router->generateUrl("routeNotFound", array());
    }

    /**
     * @expectedException Zanra\Framework\Router\Exception\InvalidParameterException
     */
    public function testInvalidParameterException()
    {
        $params = array('undefinedKey' => 'val');
        $url = $this->router->generateUrl("home", $params);
    }

    public function testGenerateUrl()
    {
        $url = $this->router->generateUrl("home", array());
        $this->assertTrue(is_string($url));
    }
}
