<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Template\Exception;

/**
 * Zanra TemplateDirectoryNotFoundException
 *
 * @author Targalis
 */
class TemplateDirectoryNotFoundException extends \Exception
{
    public function __construct($message = null)
    {
        parent::__construct($message, 404);
    }
}
