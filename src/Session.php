<?php
namespace Zanra\Framework;

class Session
{
  private $flashname = null;
  
  public function __Construct()
  { 
    $this->flash = new \Zanra\Framework\Flash();
    $this->flashname = $this->flash->getName();
  }
  
  public function start()
  {
    if (!session_start()) {
      throw new \RuntimeException('failed to start the session');
    }
    
    $_SESSION[$this->flashname] = isset($_SESSION[$this->flashname]) ? $_SESSION[$this->flashname] : $this->flash;
  }
  
  public function close()
  {
    session_write_close();
  }
  
  public function set($key, $val)
  {
    $_SESSION[$key] = $val;
  }
  
  public function get($key)
  {
    $val = null;
    if(isset($_SESSION[$key])) {
      $val = $_SESSION[$key];
    }
    
    return $val;
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
    return $this->get($this->flashname);
  }
}
