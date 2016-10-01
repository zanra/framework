<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\UrlBag;

/**
 * Zanra UrlBagInterface
 *
 * @author Targalis
 *
 */
interface UrlBagInterface
{
    /**
     * Get the current full url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get the current path
     *
     * @return string
     */
    public function getPath();

    /**
     * Get asset path
     *
     * @return string
     */
    public function getAssetPath();

    /**
     * Get the current base url
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Get the current base path
     *
     * @return string
     */
    public function getBasePath();
}
