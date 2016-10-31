<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zanra\Framework\Translator\Translator;
use Zanra\Framework\FileLoader\FileLoader;

/**
 * UrlBagTest
 *
 * @author Targalis
 *
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $fileLoader;

    protected $translator;

    protected function setUp()
    {
        $this->fileLoader = FileLoader::getInstance();
        $this->translator = new Translator($this->fileLoader);
    }

    /**
     * @expectedException Zanra\Framework\Translator\Exception\TranslationDirectoryNotFoundException
     */
    public function testTranslationDirectoryNotFoundException()
    {
        $this->translator->setTranslationDir('wrongDirectoryPath');
    }

    /**
     * @expectedException Zanra\Framework\Translator\Exception\TranslationEmptyLocaleException
     */
    public function testTranslationEmptyLocaleException()
    {
        $this->translator->translate('already', null);
    }

    /**
     * @expectedException Zanra\Framework\Translator\Exception\TranslationFileNotFoundException
     */
    public function testTranslationFileNotFoundException()
    {
        $this->translator->setTranslationDir(__DIR__ . "/fixtures");
        $this->translator->translate('welcome', 'en');
    }

    public function testTranslate()
    {
        $this->translator->setTranslationDir(__DIR__ . "/fixtures");
        $trans = $this->translator->translate('welcome', 'fr');

        $this->assertEquals("bienvenue", $trans);
    }
}
