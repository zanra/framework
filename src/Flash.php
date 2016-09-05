<?php
namespace Zanra\Framework;

class Flash
{
  private $name = '_flash';
  
  private $flashes = array();
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function add($key, $val)
  {
    $this->flashes[$key] = $val;
  }
  
  public function get($key)
  {
    $flash = null;
    
    if (isset($this->flashes[$key])) {
      $flash = $this->flashes[$key];
      
      unset($this->flashes[$key]);
    }
    
    return $flash;
  }
  
  public function all()
  {
    $all = $this->flashes;
    
    $this->flashes = array();
    
    return $all;
  }
}
