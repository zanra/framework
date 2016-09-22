<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Session\Flash;

use Zanra\Framework\Session\Flash\FlashInterface;

class Flash implements FlashInterface
{
    /**
     * @var string
     */
	 private $name = '_flash';
    
    /**
     * @var array
     */
    private $flashes = array();
    
    /**
     * Render flash name.
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set flash name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Add new object in flash.
     *
     * @param string $key
     * @param object $val
     */
    public function add($key, $val)
    {
        $this->flashes[$key] = $val;
    }
    
    /**
     * Get object from flash.
     *
     * @param string $key
     * 
     * @return object $flash
     */
    public function get($key)
    {
        $flash = null;
    
        if (isset($this->flashes[$key])) {
            $flash = $this->flashes[$key];
      
            unset($this->flashes[$key]);
        }
    
        return $flash;
    }
    
    /**
     * Get all object from flash.
     * 
     * @return object array $all
     */
    public function all()
    {
        $all = $this->flashes;
    
        $this->flashes = array();
    
        return $all;
    }
}
