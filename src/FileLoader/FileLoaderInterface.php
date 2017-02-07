<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\FileLoader;

/**
 * Zanra FileLoaderInterface
 *
 * @author Targalis
 */
interface FileLoaderInterface
{
    /**
     * Load a file
     *
     * @param string $var path of the file to load
     *
     * @return object
     */
    public function load($var);
}
