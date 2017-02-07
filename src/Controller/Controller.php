<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Controller;

use Zanra\Framework\Application\Application;

/**
 * Zanra Abstract Controller
 *
 * @author Targalis
 */
abstract class Controller
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
    }

    /**
     * Helper function to forward in a new controller method.
     *
     * @param $controller
     * @param array      $params
     *
     * @return mixed|string
     */
    public function forward($controller, array $params = array())
    {
        return $this->app->renderController($controller, $params);
    }

    /**
     * Helper function to render a view.
     *
     * @param $filename
     * @param array    $vars
     *
     * @return string
     */
    public function render($filename, array $vars = array())
    {
        return $this->app->renderView($filename, $vars);
    }

    /**
     * Redirect to new route
     *
     * @param $route
     * @param array $params
     */
    public function redirect($route, array $params = array())
    {
        \header('Location: ' .$this->app->path($route, $params));
        exit();
    }
}
