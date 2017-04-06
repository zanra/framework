<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Application\Registry;

/**
 * Zanra RegistryInterface
 *
 * @author Targalis
 */
interface RegistryInterface
{
    /**
     * Get key from registry
     *
     * @param string $name
     *
     * @return object
     */
    public function get($name);

    /**
     * Get all keys from registry
     *
     * @return object[]
     */
    public function all();

    /**
     * Add key in registry
     *
     * @param string    $name
     * @param object    $callback
     */
    public function add($name, $callback);

    /**
     * Remove key from registry
     *
     * @param string $name
     */
    public function remove($name);
}
