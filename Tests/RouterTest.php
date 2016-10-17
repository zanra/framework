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
    public function testRouteFoundException()
    {
        $loader = FileLoader::getInstance();
        $routes = $loader->load(__DIR__ . "/../Tests/Mocks/routes.ini");
        $urlBag = new UrlBag("http://127.0.0.1");
        $router = new Router($routes, $urlBag);
        $this->assertInternalType('array',$router->matchRequest());
    }

    public function testRouteNotFoundException()
    {
        $loader = FileLoader::getInstance();
        $routes = $loader->load(__DIR__ . "/../Tests/Mocks/routes.ini");
        $urlBag = new UrlBag("http://127.0.0.1/route/not/found/test/case");
        $router = new Router($routes, $urlBag);
        $this->assertFalse($router->matchRequest());
    }
}
