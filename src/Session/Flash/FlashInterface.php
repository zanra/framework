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
    public function getName();
    
    public function setName($name);
    
    public function add($key, $val);
    
    public function get($key);
    
    public function all();
}
