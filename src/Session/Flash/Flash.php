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

/**
 * Zanra flash 
 * @author Targalis
 *
 */
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
     * (non-PHPdoc)
     * @see \Zanra\Framework\Session\Flash.FlashInterface::getName()
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zanra\Framework\Session\Flash.FlashInterface::setName()
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zanra\Framework\Session\Flash.FlashInterface::add()
     */
    public function add($key, $val)
    {
        $this->flashes[$key] = $val;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zanra\Framework\Session\Flash.FlashInterface::get()
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
     * (non-PHPdoc)
     * @see \Zanra\Framework\Session\Flash.FlashInterface::all()
     */
    public function all()
    {
        $all = $this->flashes;
    
        $this->flashes = array();
    
        return $all;
    }
}
