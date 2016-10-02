<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Translator;

use Zanra\Framework\Translator\TranslatorInterface;
use Zanra\Framework\Translator\Exception\TranslationFileNotFoundException;
use Zanra\Framework\Translator\Exception\TranslationEmptyLocaleException;
use Zanra\Framework\FileLoader\FileLoader;
use Zanra\Framework\FileLoader\FileLoaderInterface;

/**
 * Zanra Translator
 *
 * @author Targalis
 *
 */
class Translator implements TranslatorInterface
{
    /**
     * @var FileLoader
     */
    private $fileLoader;

    /**
     * @var object
     */
    private $translations = array();

    /**
     * @var string
     */
    private $translationDir;

    /**
     * Constructor
     *
     * @param FileLoaderInterface $fileLoader
     */
    public function __Construct(FileLoaderInterface $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Translator.TranslatorInterface::setTranslationDir()
     */
    public function setTranslationDir($translationDir)
    {
        $this->translationDir = $translationDir;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Translator.TranslatorInterface::getTranslationDir()
     */
    public function getTranslationDir()
    {
        return $this->translationDir;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Translator.TranslatorInterface::translate()
     */
    public function translate($message, $locale = null)
    {
        if (null == $locale) {
            throw new TranslationEmptyLocaleException(
                sprintf('translation locale can\'t be empty'));
        }

        $locale = strtolower($locale);
        $translationFile = "{$this->getTranslationDir()}/messages.{$locale}.ini";

        if (!file_exists($translationFile)) {
            throw new TranslationFileNotFoundException(
                sprintf('translation file "%s" not found', $translationFile));
        }

        if (!isset($this->translation[$locale])) {
            $this->translation[$locale] = $this->fileLoader->load($translationFile);    
        }
        
        $trans = $this->translation[$locale];

        return !empty($trans->$message) ? $trans->$message : $message;
    }
}
