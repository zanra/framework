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

use Zanra\Framework\Application\Registry\Exception\RegistryKeyNotFoundException;
use Zanra\Framework\Application\Registry\Exception\RegistryDuplicatedKeyException;

/**
 * Registry
 *
 * @author Targalis
 */
class Registry implements RegistryInterface
{
    /**
     * @var object[]
     */
    private $entries = array();

    /**
     * @param string $name
     *
     * @return object
     *
     * @throws RegistryKeyNotFoundException
     */
    public function get($name)
    {
        if (! in_array($name, array_keys($this->entries))) {
            throw new RegistryKeyNotFoundException(
                sprintf('key "%s" not found in registry', $name)
            );
        }

        return $this->entries[$name];
    }

    /**
     * @return object[]
     */
    public function all()
    {
        return $this->entries;
    }

    /**
     * @param string    $name
     * @param object    $callback
     *
     * @throws RegistryDuplicatedKeyException
     */
    public function add($name, $callback)
    {
        if (in_array($name, array_keys($this->entries))) {
            throw new RegistryDuplicatedKeyException(
                sprintf('Cannot redeclare key "%s" in registry', $name)
            );
        }

        $this->entries[$name] = $callback;
    }

    /**
     * @param string  $name
     *
     * @throws RegistryKeyNotFoundException
     */
    public function remove($name)
    {
        if (! in_array($name, array_keys($this->entries))) {
            throw new RegistryKeyNotFoundException(
                sprintf('key "%s" not found in registry', $name)
            );
        }

        unset($this->entries[$name]);
    }
}
