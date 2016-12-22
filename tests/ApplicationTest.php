<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zanra\Framework\Application\Application;

/**
 * ApplicationTest
 *
 * @author Targalis
 *
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $application;

    protected function setUp()
    {
        $this->application = Application::getInstance();
    }

    /**
     * @expectedException Zanra\Framework\Application\Exception\LoadConfigFileException
     */
    public function testMvcHandleWithoutCallingLoadConfig()
    {
        $this->application->mvcHandle(new ErrorWrapperTest());
    }

    public function testLoadConfig()
    {
        $this->application->loadConfig(__DIR__ . "/fixtures/resources.ini");
        $this->application->mvcHandle(new ErrorWrapperTest());
    }

    public function testTranslate()
    {
        $trans = $this->application->translate('welcome');

        $this->assertEquals("bienvenue", $trans);
    }
}
