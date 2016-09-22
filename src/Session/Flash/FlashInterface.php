<?php
namespace Zanra\Framework\Session\Flash;

interface FlashInterface
{
    public function getName();
    
    public function setName($name);
    
    public function add($key, $val);
    
    public function get($key);
    
    public function all();
}
