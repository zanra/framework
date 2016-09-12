<?php
namespace Zanra\Framework\Translator;

interface TranslatorInterface
{
    public function getTranslationDir();
    
    public function setTranslationDir($translationDir);
    
    public function translate($message, $locale);
}
