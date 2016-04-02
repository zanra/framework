<?php
namespace Zanra\Framework\Translator\Exception;

class TranslationFileNotFoundException extends \ErrorException
{
  public function __construct($message = null)
  {
    parent::__construct($message, 404);
  }
}
