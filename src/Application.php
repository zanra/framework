<?php
namespace Zanra\Framework;

use Zanra\Framework\UrlBag\UrlBag;
use Zanra\Framework\Router\Router;
use Zanra\Framework\Session;
use Zanra\Framework\FileLoader\FileLoader;
use Zanra\Framework\Translator\Translator;

class Application
{
  const RES_LOCALE_KEY = "default.locale";
  const RES_APPLICATION_KEY = "application";
  const RES_ROUTING_KEY = "routing.file";
  const RES_FILTERS_KEY = "filters.file";
  const RES_TRANSLATION_KEY = "translation.dir";
  const RES_TEMPLATE_KEY = "template.dir";
  const RES_CACHE_KEY = "cache.dir";
  
  const SESSION_LOCALE_KEY = "_locale";
  
  private $resources = array();
  private $routes = array();
  private $filters = array();
  
  private $route;
  private $controller;
  private $action;
  private $params = array();
  
  private $urlBag;
  private $router;
  private $fileLoader;
  private $configRealPath = null;
  
  private $translator;
  private $defaultLocale;
  
  private $template;
  
  private $configLoaded  = false;
  private $filtersLoaded = false;
  
  private static $_instance = null;
  
  private function __Construct()
  {
    $this->urlBag     = new UrlBag();
    $this->session    = new Session();
    $this->fileLoader = FileLoader::getInstance();
  }
  
  private function hasSession()
  {
    if (php_sapi_name() !== 'cli') {
      if (version_compare(phpversion(), '5.4.0', '>=')) {
        return session_status() === PHP_SESSION_ACTIVE ? true : false;
      } else {
        return session_id() === '' ? false : true;
      }
    }
    return false;
  }
  
  private function getConfigRealPath()
  {
    if (false === $this->configLoaded)
      throw new \Zanra\Framework\Exception\LoadConfigFileException(
        sprintf('Please call "%s"', __CLASS__ . "::loadConfig"));
       
    return $this->configRealPath;
  }
  
  private function loadFilters()
  {
    foreach ($this->getFilters() as $class => $method) {
      $filterNamespaceClass = "\\Filter\\{$class}Filter";
      $filterClass = class_exists($filterNamespaceClass) ? new $filterNamespaceClass() : null;
      
      if (null === $filterClass)
        throw new \Zanra\Framework\Exception\FilterNotFoundException(
          sprintf('Class "%s" not found', $filterNamespaceClass));
      
      if (!method_exists($filterClass, $method))
        throw new \Zanra\Framework\Exception\FilterMethodNotFoundException(
          sprintf('"Unable to find Method "%s" in "%s" scope', $method, $filterNamespaceClass));
      
      call_user_func_array(array($filterClass, $method), array($this));
    }
  }
  
  public function loadConfig($config)
  {
    if (false !== $this->configLoaded) {
      return;
    }
    
    $this->configLoaded   = true;
    $this->configRealPath = dirname($config);
    $this->resources      = $this->fileLoader->load($config);
    
    if (!isset($this->resources->{self::RES_APPLICATION_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
        sprintf('section key "[%s]" not declared in resources', self::RES_APPLICATION_KEY));
    
    if (!isset($this->resources->{self::RES_APPLICATION_KEY}->{self::RES_ROUTING_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
        sprintf('key "%s" not declared in resources [%s] section', self::RES_ROUTING_KEY, self::RES_APPLICATION_KEY));
      
    if (!isset($this->resources->{self::RES_APPLICATION_KEY}->{self::RES_FILTERS_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
        sprintf('key "%s" not declared in resources [%s] section', self::RES_FILTERS_KEY, self::RES_APPLICATION_KEY));
    
    $routesCfg            = $this->configRealPath . DIRECTORY_SEPARATOR . $this->resources->{self::RES_APPLICATION_KEY}->{self::RES_ROUTING_KEY};
    $filtersCfg           = $this->configRealPath . DIRECTORY_SEPARATOR . $this->resources->{self::RES_APPLICATION_KEY}->{self::RES_FILTERS_KEY};
    
    if (!file_exists($routesCfg))
      throw new \Zanra\Framework\FileLoader\Exception\FileNotFoundException(
      sprintf('File "%s" with key "%s" declared in resources [%s] section not found', $routesCfg, self::RES_ROUTING_KEY, self::RES_APPLICATION_KEY));
    
    if (!file_exists($filtersCfg))
      throw new \Zanra\Framework\FileLoader\Exception\FileNotFoundException(
        sprintf('File "%s" with key "%s" declared in resources [%s] section not found', $filtersCfg, self::RES_FILTERS_KEY, self::RES_APPLICATION_KEY));
    
    if (!isset($this->resources->{self::RES_APPLICATION_KEY}->{self::RES_LOCALE_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
        sprintf('resource key "%s" not declared in resources [%s] section', self::RES_LOCALE_KEY, self::RES_APPLICATION_KEY));
    
    $this->routes         = $this->fileLoader->load($routesCfg);
    $this->filters        = $this->fileLoader->load($filtersCfg);
    $this->defaultLocale  = $this->resources->{self::RES_APPLICATION_KEY}->{self::RES_LOCALE_KEY};
  }
  
  public function mvcHandle()
  {
    if (null !== $this->router) {
      return;
    }
  
    if (false === $this->configLoaded)
      throw new \Zanra\Framework\Exception\LoadConfigFileException(
        sprintf('Please call "%s" before call "%s"', __CLASS__ . "::loadConfig", __METHOD__));
  
    $this->router     = new Router($this->routes, $this->urlBag);
  
    // match current request
    if (false === $matches = $this->router->matchRequest()) {
      throw new \Zanra\Framework\Router\Exception\RouteNotFoundException(
        sprintf('No route found for "%s"', $this->urlBag->getUrl()));
    }

    $this->route      = $matches["route"];
    $this->controller = $matches["controller"];
    $this->action     = $matches["action"];
    $this->params     = $matches["params"];
  
    print $this->renderController("{$this->getcontroller()}:{$this->getAction()}", $this->getParams());
  }
  
  /**
   *  Get route
   *  return String
   *  app->getRoute()
   */
  public function getRoute()
  {
    return $this->route;
  }
  
  /**
   *  Get controller
   *  return current controller
   *  app->getController()
   */
  public function getController()
  {
    return $this->controller;
  }
  
  /**
   *  Get action
   *  return String
   *  app->getAction()
   */
  public function getAction()
  {
    return $this->action;
  }
  
  /**
   *  Get params
   *  return Array
   *  app->getParams()
   */
  public function getParams()
  {
    return $this->params;
  }
  
  /**
   *  Get route Collection
   *  return Array
   *  app->getRoutes()
   */
  public function getRoutes()
  {
    return $this->getRoutes();
  }
  
  /**
   *  Get resource Collection
   *  return Array
   *  app->getResources()
   */
  public function getResources()
  {
    return $this->resources;
  }
  
  /**
   *  Get filter Collection
   *  return Array
   *  app->getFilters()
   */
  public function getFilters()
  {
    return $this->filters;
  }
  
  /**
   *  Get session
   *  return Session instance
   *  app->getSession()
   */
  public function getSession()
  {
    return $this->session;
  }
  
  /**
   *  Get url
   *  return String
   *  app->getUrl()
   */
  public function getUrl()
  {
    return $this->urlBag->getUrl();
  }
  
  /**
   *  Get path
   *  return String
   *  app->getPath()
   */
  public function getPath()
  {
    return $this->urlBag->getPath();
  }
  
  /**
   *  Get asset path
   *  return String
   *  app->getAssetPath()
   */
  public function getAssetPath()
  {
    return $this->urlBag->getAssetPath();
  }

  /**
   *  Get base url
   *  return String
   *  app->getBaseUrl()
   */
  public function getBaseUrl()
  {
    return $this->urlBag->getBaseUrl();
  }

  /**
   * Get base path
   * return String
   * app->getBasePath()
   */
  public function getBasePath()
  {
    return $this->urlBag->getBasePath();
  }

  /**
   *  generate url
   *  params string $route, array $params
   *  return String
   *  app->url($route, $params = array())
   */
  public function url($route, $params = array())
  {
    return $this->urlBag->getBaseUrl() . $this->router->generateUrl($route, $params);
  }
  
  /**
   *  generate path
   *  params string $route, array $params
   *  return String
   *  app->path($route, $params = array())
   */
  public function path($route, $params = array())
  {
    return $this->urlBag->getBasePath() . $this->router->generateUrl($route, $params);
  }
  
  /**
   *  generate asset
   *  params: string $path
   *  return String
   *  app->asset($path)
   */
  public function asset($path)
  {
    return $this->urlBag->getAssetPath() . $path;
  }
  
  /**
   *  renderController (call a new controller)
   *  params string $controller, array $params
   *  return response
   *  app->renderController($controller, array $params = array())
   */
  public function renderController($controller, array $params = array())
  {
    if (false === $this->filtersLoaded) {
      $this->loadFilters();
      $this->filtersLoaded = true;
    }
    
    $parts = explode(':', $controller);
    
    $controller = "\\Controller\\{$parts[0]}Controller";
    $action = "{$parts[1]}Action";
    
    // Check Controller\Zanra\Framework\Controller
    $controllerClass = class_exists($controller) ? new $controller() : null;
    if (null === $controllerClass)
      throw new \Zanra\Framework\Exception\ControllerNotFoundException(sprintf('"%s" not found', $controller));
    
    // Check Action
    if (!method_exists($controllerClass, "{$action}"))
      throw new \Zanra\Framework\Exception\ControllerActionNotFoundException(sprintf('unable to find "%s" in "%s" scope', $action, $controller));
    
    // Method Args
    $methodArgs = array();
    
    $reflexion = new \ReflectionMethod($controller, $action);
    foreach ($reflexion->getParameters() as $p) {
      $methodArgs[$p->getName()] = null;
      if ($p->isOptional()) {
        $methodArgs[$p->getName()] = $p->getDefaultValue();
      } else {
        if (!isset($params[$p->getName()]) && $params[$p->getName()] !== null)
          throw new \Zanra\Framework\Router\Exception\ControllerActionMissingDefaultParameterException(sprintf("missing '%s:%s' argument '%s' value (because there is no default value or because there is a non optional argument after this one)", $controller, $action, $p->getName()));
      }
    }
    
    $params = array_merge($methodArgs, $params);
    
    // Call Action
    $callAction = call_user_func_array(array($controllerClass, $action), $params);
    if (!isset($callAction))
      throw new \Zanra\Framework\Exception\ControllerBadReturnResponseException(sprintf('"%s:%s" must return a response. null given', $controller, $action));
    
    return $callAction;
  }
  
  /**
   *  renderView (call a new template)
   *  params string $filename, array $vars
   *  return response
   *  app->renderView($filename, array $vars = array())
   */
  public function renderView($filename, array $vars = array())
  {
    if (!isset($this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_TEMPLATE_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
      sprintf('key "%s" not declared in resources [%s] section', self::RES_TEMPLATE_KEY, self::RES_APPLICATION_KEY));
      
    if (!isset($this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_CACHE_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
      sprintf('key "%s" not declared in resources [%s] section', self::RES_CACHE_KEY, self::RES_APPLICATION_KEY));
    
    if (null === $this->template) {
      $templateDir = $this->getConfigRealPath() . DIRECTORY_SEPARATOR . $this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_TEMPLATE_KEY};
      $cacheDir = $this->getConfigRealPath() . DIRECTORY_SEPARATOR . $this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_CACHE_KEY};
      $this->template = new \Zanra\Framework\Template($templateDir, $cacheDir);
    }
    
    return $this->template->render($filename, $vars);
  }
  
  /**
   *  translate
   *  params string key
   *  return String
   *  app->translate($message)
   */
  public function translate($message, $locale = null)
  {
    if (!isset($this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_TRANSLATION_KEY}))
      throw new \Zanra\Framework\Exception\ResourceKeyNotFoundException(
      sprintf('resource key "%s" not declared in resources [%s] section', self::RES_TRANSLATION_KEY, self::RES_APPLICATION_KEY));
          
    if (null === $this->translator) {
      $this->translator = new Translator($this->fileLoader);
      
      $translationDir = $this->getConfigRealPath() . DIRECTORY_SEPARATOR . $this->getResources()->{self::RES_APPLICATION_KEY}->{self::RES_TRANSLATION_KEY};
      $this->translator->setTranslationDir($translationDir);
    }
  
    if (null === $locale) {
      if (!$this->hasSession()) {
        $locale = $this->defaultLocale;
      } else {
        $sessionLocale = $this->getSession()->get(self::SESSION_LOCALE_KEY);
        $locale = !empty($sessionLocale) ? $sessionLocale : $this->defaultLocale;
      }
    }

    return $this->translator->translate($message, $locale);
  }
  
  public static function getInstance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new Application();
    }
    return self::$_instance;
  }
}
