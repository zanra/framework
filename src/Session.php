<?php
namespace Zanra\Framework;

class Session
{
  private static $_instance = null;
  
  private function __Construct()
  {
    session_write_close();
    session_start();
  }
  
  public function set($key, $val)
  {
    $_SESSION[$key] = $val;
  }
  
  public function get($key)
  {
    $msg = null;
    if(isset($_SESSION[$key])) {
      $msg = $_SESSION[$key];
    }
    return $msg;
  }
  
  public function destroy()
  {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
      );
    }
    
    session_destroy();
  }
  
  /**
    * Get flash message
    * params string key
    * return Flash instance
    * session->getFlash()
  */
  public function getFlash()
  {
    return \Core\Flash::getInstance();
  }
  
  public static function getInstance()
  {
    if(is_null(self::$_instance)) {
      self::$_instance = new Session();
    }
    return self::$_instance;
  }
}
