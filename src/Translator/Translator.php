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

use Zanra\Framework\FileLoader\FileLoader;
use Zanra\Framework\Translator\TranslatorInterface;
use Zanra\Framework\Translator\Exception\TranslationFileNotFoundException;
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
    private $translation;

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
    public function translate($message, $locale)
    {
        $locale = strtolower($locale);
        $translationFile = "{$this->getTranslationDir()}/messages.{$locale}.ini";

        if (!file_exists($translationFile))
            throw new TranslationFileNotFoundException(
                sprintf('translation file "%s" not found', $translationFile));

        $this->translation = $this->fileLoader->load($translationFile);

        return !empty($this->translation->$message) ? $this->translation->$message : $message;
    }
}
