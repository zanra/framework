<?php
namespace Zanra\Framework;

class Flash
{
  const FLASH = '_flash';
  private static $_instance = null;
  
  public function set($key, $val)
  {
    $_SESSION[self::FLASH][$key] = $val;
  }
  
  public function get($key)
  {
    $msg = array();
    if (isset($_SESSION[self::FLASH][$key])) {
      $msg = $_SESSION[self::FLASH][$key];
      $_SESSION[self::FLASH] = array();
    }
    
    return $msg;
  }
  
  public static function getInstance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new Flash();
    }
    
    return self::$_instance;
  }
}
