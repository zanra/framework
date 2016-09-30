<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Application\Exception;

class ControllerBadReturnResponseException extends \ErrorException
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
