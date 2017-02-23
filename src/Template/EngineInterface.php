<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Template;

/**
 * Zanra EngineInterface
 *
 * @author Targalis
 */
interface EngineInterface
{
    /**
     * Get environment
     *
     * @return environment
     */
    public function getEnvironment();

    /**
     * Render a template view
     *
     * @param string $filename
     * @param array  $vars
     */
    public function render($filename, array $vars = array());
}
