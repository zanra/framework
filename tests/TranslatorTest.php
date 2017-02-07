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
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $fileLoader;

    protected $translator;

    protected function setUp()
    {
        $this->fileLoader = new FileLoader();
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
        $this->translator->translate('already', array(), null);
    }

    /**
     * @expectedException Zanra\Framework\Translator\Exception\TranslationFileNotFoundException
     */
    public function testTranslationFileNotFoundException()
    {
        $this->translator->setTranslationDir(__DIR__ . "/fixtures/translation");
        $this->translator->translate('welcome', array(), 'es');
    }

    public function testTranslateWithVar()
    {
        $this->translator->setTranslationDir(__DIR__ . "/fixtures/translation");
        $trans = $this->translator->translate('test_text', array('vendor_name' => 'phpunit'), 'fr');

        $this->assertEquals("utilisation de phpunit pour test unitaire", $trans);
    }

    public function testTranslate()
    {
        $this->translator->setTranslationDir(__DIR__ . "/fixtures/translation");
        $trans = $this->translator->translate('welcome', array(), 'fr');

        $this->assertEquals("bienvenue", $trans);
    }
}
