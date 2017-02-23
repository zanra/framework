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
 * Template engine
 *
 * @author Targalis
 */
class Template implements TemplateInterface
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * Constructor
     *
     * @param EngineInterface $engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Get Engine
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set Engine
     *
     * @return EngineInterface
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param string $filename
     * @param array  $vars
     *
     * @return string
     */
    public function render($filename, array $vars = array())
    {
        return $this->engine->getEnvironment()->render($filename, $vars);
    }
}
