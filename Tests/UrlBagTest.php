<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zanra\Framework\UrlBag\UrlBag;

/**
 * UrlBagTest
 *
 * @author Targalis
 *
 */
class UrlBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Zanra\Framework\UrlBag\Exception\EmptyURLException
     */
    public function testEmptyURLException()
    {
        $UrlBag = new UrlBag();
    }

    /**
     * @expectedException Zanra\Framework\UrlBag\Exception\BadURLFormatException
     */
    public function testBadURLFormatException()
    {
        $UrlBag = new UrlBag("http:/127.0.0.1");
    }
}
