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

use Zanra\Framework\Template\Exception\TemplateDirectoryNotFoundException;
use Zanra\Framework\Application\Application;

/**
 * Template engine
 *
 * @author Targalis
 *
 */
class Template implements TemplateInterface
{
    /**
     * @var Twig_Loader_Filesystem
     */
    private $loader;

    /**
     * @var Twig_Environment
     */
    private $engine;

    /**
     * @var Application
     */
    private $application;

    /**
     * Constructor
     *
     * @param string $templateDir
     * @param bool $cacheDir
     *
     * @throws TemplateDirectoryNotFoundException
     */
    public function __Construct($templateDir, $cacheDir = false)
    {
        if (realpath($templateDir) === false) {
            throw new TemplateDirectoryNotFoundException(
                sprintf('template directory not found in "%s"', $templateDir));
        }

        $this->application = Application::getInstance();
        $this->loader = new \Twig_Loader_Filesystem($templateDir);
        $this->engine = new \Twig_Environment($this->loader, array(
            'cache' => $cacheDir,
            'auto_reload' => true,
            'strict_variables' => true
        ));

        $this->engine->addGlobal('app', $this->application);
    }

    /**
     * getEngine
     *
     * @return Twig_Environment
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $filename
     * @param array $vars
     *
     * @return string
     */
    public function render($filename, array $vars = array())
    {
        return $this->getEngine()->render($filename, $vars);
    }
}
