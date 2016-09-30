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

interface FlashInterface
{
	/**
	 * Render flash name.
	 * @return string
	 */
    public function getName();
    
    /**
     * Set flash name
     * @param $name
     */
    public function setName($name);
    
    /**
     * @param string $key
     * @param object $val
     */
    public function add($key, $val);
    
    /**
     * @param string $key
     * @return object $flash
     */
    public function get($key);
    
    /**
     * Get all flashes object
     * @return object array
     */
    public function all();
}
