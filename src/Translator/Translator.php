<?php
namespace Zanra\Framework\Translator;

use Zanra\Framework\FileLoader\FileLoader;
use Zanra\Framework\Translator\TranslatorInterface;
use Zanra\Framework\Translator\Exception\TranslationFileNotFoundException;
use Zanra\Framework\FileLoader\FileLoaderInterface;

class Translator implements TranslatorInterface
{
    private $fileLoader;
    
    private $translation;
    
    private $translationDir;
  
    public function __Construct(FileLoaderInterface $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }
  
    public function setTranslationDir($translationDir)
    {
        $this->translationDir = $translationDir;
    }
  
    public function getTranslationDir()
    {
        return $this->translationDir;
    }
  
    /**
     * translate
     * params string key
     * return String
     * app->translate( $message )
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
