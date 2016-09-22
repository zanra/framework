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

interface UrlBagInterface
{
    /**
     * getUrl
     */
    public function getUrl();
  
    /**
     * getPath
     */
    public function getPath();
  
    /**
     * getAssetPath
     */
    public function getAssetPath();
  
    /**
     * getBaseUrl
     */
    public function getBaseUrl();

    /**
     * getBasePath
     */
    public function getBasePath();
}
