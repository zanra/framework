<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
