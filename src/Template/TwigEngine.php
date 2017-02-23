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
class TwigEngine implements EngineInterface
{
    /**
     * @var Twig_Environment
     */
    private $environment;

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
        $this->environment = new \Twig_Environment($loader);

        $this->environment->setCache($cacheDir);
        $this->environment->enableAutoReload();
        $this->environment->enableStrictVariables();

        $this->environment->addGlobal('app', $application);
    }

    /**
     * Get Environment
     *
     * @return Twig_Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $filename
     * @param array  $vars
     *
     * @return string
     */
    public function render($filename, array $vars = array())
    {
        return $this->environment->render($filename, $vars);
    }
}
