<?php
namespace Zanra\Framework\Session;

interface SessionInterface
{
    public function start();
    
    public function close();
    
    public function set($key, $val);
    
    public function get($key);
    
    public function destroy();
    
    public function getFlash();
}
