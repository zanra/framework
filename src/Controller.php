<?php
namespace Zanra\Framework;

abstract class Controller
{
  protected $app;
  
  public function __Construct()
  {
    $this->app = Application::getInstance();
  }
  
  public function forward($controller, $params = array())
  {
    return $this->app->renderController($controller, $params);
  }
  
  public function render($filename, $vars = array())
  {
    return $this->app->renderView($filename, $vars);
  }
  
  public function redirect($route, $params = array())
  {
    \header('Location: ' . $this->app->path($route, $params));
    exit();
  }
}
