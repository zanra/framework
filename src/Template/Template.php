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
 */
class Template implements TemplateInterface
{
    /**
     * @var Twig_Environment
     */
    private $engine;

    /**
     * Constructor
     *
     * @param string $templateDir
     * @param bool   $cacheDir
     *
     * @throws TemplateDirectoryNotFoundException
     */
    public function __construct(Application $application)
    {
        $templateDir = $application->getTemplateDir();
        $cacheDir = $application->getCacheDir();

        if (realpath($templateDir) === false) {
            throw new TemplateDirectoryNotFoundException(
                sprintf('template directory not found in "%s"', $templateDir)
            );
        }

        $loader = new \Twig_Loader_Filesystem($templateDir);
        $this->engine = new \Twig_Environment($loader);

        $this->engine->setCache($cacheDir);
        $this->engine->enableAutoReload();
        $this->engine->enableStrictVariables();

        $this->engine->addGlobal('app', $application);
    }

    /**
     * Get Engine
     *
     * @return Twig_Environment
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $filename
     * @param array  $vars
     *
     * @return string
     */
    public function render($filename, array $vars = array())
    {
        return $this->getEngine()->render($filename, $vars);
    }
}
