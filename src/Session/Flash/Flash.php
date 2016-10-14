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

/**
 * Zanra flash
 *
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $key
     * @param object $val
     */
    public function add($key, $val)
    {
        $this->flashes[$key] = $val;
    }

    /**
     * @param string $key
     * @return mixed|null
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
     * @return array
     */
    public function all()
    {
        $all = $this->flashes;

        $this->flashes = array();

        return $all;
    }
}
