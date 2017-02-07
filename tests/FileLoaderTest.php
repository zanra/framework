<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zanra\Framework\FileLoader\FileLoader;

/**
 * UrlBagTest
 *
 * @author Targalis
 */
class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $fileLoader;

    protected function setUp()
    {
        $this->fileLoader = new FileLoader();
    }

    /**
     * @expectedException Zanra\Framework\FileLoader\Exception\FileNotFoundException
     */
    public function testFileNotFoundException()
    {
        $this->fileLoader->load(__DIR__ . "/fixtures/no_routes.ini");
    }

    /**
     * @expectedException Zanra\Framework\FileLoader\Exception\WrongFileExtensionException
     */
    public function testWrongFileExtensionException()
    {
        $this->fileLoader->load(__DIR__ . "/fixtures/routes.no");
    }

    public function testLoad()
    {
        $routes = $this->fileLoader->load(__DIR__ . "/fixtures/routes.ini");
        $this->assertNotEmpty($routes);
        $this->assertInstanceOf('stdClass', $routes);
    }
}
