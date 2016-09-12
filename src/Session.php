<?php
namespace Zanra\Framework;

class Session
{
    private $started = false;
  
    private $closed = false;

    private $flashname = null;
  
    public function __Construct()
    { 
        $this->flash = new \Zanra\Framework\Flash();
        $this->flashname = $this->flash->getName();
    }

    public function start()
    {
        if ($this->started) {
            return true;
        }

        // This prevents PHP from attempting to send the headers again
        // when session_write_close is called
        if ($this->closed) {
            ini_set('session.use_only_cookies', false);
            ini_set('session.use_cookies', false);
            ini_set('session.cache_limiter', null);
        }

        if (!session_start()) {
            throw new \RuntimeException('failed to start the session');
        }
    
        $_SESSION[$this->flashname] = isset($_SESSION[$this->flashname]) ? $_SESSION[$this->flashname] : $this->flash;

        $this->closed = false;
        $this->started = true;
    }
  
    public function close()
    {
        if (!$this->started) {
            return true;
        }

        session_write_close();

        $this->closed = true;
        $this->started = false;
    }
  
    public function set($key, $val)
    {
        if (!$this->started) {
            $this->start();
        }

        $_SESSION[$key] = $val;
    }
  
    public function get($key)
    {
        if (!$this->started) {
            $this->start();
        }
        
        $val = null;
        if (isset($_SESSION[$key])) {
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
        
        $this->closed = false;
        $this->started = false;
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
