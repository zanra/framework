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

use Zanra\Framework\Template\EngineInterface;

/**
 * Zanra TemplateInterface
 *
 * @author Targalis
 */
interface TemplateInterface
{
    /**
     * Get engine
     *
     * @return EngineInterface
     */
    public function getEngine();

    /**
     * Set engine
     *
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine);

    /**
     * Render a template view
     *
     * @param string $filename
     * @param array  $vars
     */
    public function render($filename, array $vars = array());
}
