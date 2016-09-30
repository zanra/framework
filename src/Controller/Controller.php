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
 * Abstract controller
 * @author Targalis
 *
 */
abstract class Controller
{
	/**
	 * @var \Zanra\Framework\Application\Application
	 */
	protected $app;

	/**
	 * Constructor.
	 */
	public function __Construct()
	{
		$this->app = Application::getInstance();
	}

	/**
	 * Helper function to forward in a new controller method.
	 * @param string $controller
	 * @param object[] $params
	 * @return string
	 */
	public function forward($controller, array $params = array())
	{
		return $this->app->renderController($controller, $params);
	}

	/**
	 * Helper function to render a view.
	 * @param string $filename
	 * @param object[] $vars
	 * @return string
	 */
	public function render($filename, array $vars = array())
	{
		return $this->app->renderView($filename, $vars);
	}
	
	/**
	 * Redirect to new route
	 * @param string $route
	 * @param array $params
	 */
	public function redirect($route, array $params = array())
	{
		\header('Location: ' . $this->app->path($route, $params));
		exit();
	}
}
