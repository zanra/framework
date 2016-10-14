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
use Zanra\Framework\Translator\Exception\TranslationDirectoryNotFoundException;
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
     * @var array
     */
    private $translations = array();

    /**
     * @var string
     */
    private $translationDir;

    /**
     * Translator constructor.
     *
     * @param FileLoaderInterface $fileLoader
     */
    public function __Construct(FileLoaderInterface $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }

    /**
     * @param string $translationDir
     *
     * @throws TranslationDirectoryNotFoundException
     */
    public function setTranslationDir($translationDir)
    {
        if (!is_dir($translationDir)) {
            throw new TranslationDirectoryNotFoundException(
                sprintf('Translation directory "%s" not found', $translationDir));
        }

        $this->translationDir = $translationDir;
    }

    /**
     * @return string
     */
    public function getTranslationDir()
    {
        return $this->translationDir;
    }

    /**
     * @param string $message
     * @param null $locale
     *
     * @return string
     *
     * @throws TranslationEmptyLocaleException
     * @throws TranslationFileNotFoundException
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

        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = $this->fileLoader->load($translationFile);    
        }
        
        $trans = $this->translations[$locale];

        return !empty($trans->$message) ? $trans->$message : $message;
    }
}
