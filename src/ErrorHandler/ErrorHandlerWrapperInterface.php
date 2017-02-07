<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\ErrorHandler;

/**
 * Zanra ErrorHandlerWrapperInterface
 *
 * @author Targalis
 */
interface ErrorHandlerWrapperInterface
{
    /**
     * @param $exception
     * @param null      $type
     *
     * @return mixed
     */
    public function wrap($exception, $type = null);
}
