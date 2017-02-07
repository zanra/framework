<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Router;

use Zanra\Framework\UrlBag\UrlBagInterface;

/**
 * Zanra RouterInterface
 *
 * @author Targalis
 */
interface RouterInterface
{
    /**
     * @return array|bool
     */
    public function matchRequest(UrlBagInterface $urlBag);

    /**
     * @param array $routename
     * @param array $parameters
     */
    public function generateUri($routename, array $parameters = array());
}
