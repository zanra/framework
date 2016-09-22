<?php
    
/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework;

use Zanra\Framework\UrlBag\UrlBag;
use Zanra\Framework\Router\Router;
use Zanra\Framework\Session\Session;
use Zanra\Framework\Template;
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
    
    /**
     * @var object[]
     */
    private $resources = array();
    
    /**
     * @var object[]
     */
    private $routes = array();
    
    /**
     * @var object[]
     */
    private $filters = array();
    
    /**
     * @var string
     */
    private $route;
    
    /**
     * @var string
     */
    private $controller;
    
    /**
     * @var string
     */
    private $action;
    
    /**
     * @var string[]
     */
    private $params = array();
    
    /**
     * @var \Zanra\Framework\UrlBag\UrlBag
     */
    private $urlBag;
    
    /**
     * @var \Zanra\Framework\Router\Router
     */
    private $router;
    
    /**
     * @var \Zanra\Framework\FileLoader\FileLoader
     */
    private $fileLoader;
    
    /**
     * @var string
     */
    private $configRealPath = null;
    
    /**
     * @var \Zanra\Framework\Translator\Translator
     */
    private $translator;
    
    /**
     * @var string
     */
    private $defaultLocale;
    
    /**
     * @var \Zanra\Framework\Template
     */
    private $template;
    
    /**
     * @var bool
     */
    private $configLoaded  = false;
    
    /**
     * @var bool
     */
    private $filtersLoaded = false;
    
    /**
     * @var Zanra\Framework\Application
     */
    private static $_instance = null;
    
    /**
     * Constructor.
     */
    private function __Construct()
    {
        $this->urlBag     = new UrlBag();
        $this->session    = new Session();
        $this->fileLoader = FileLoader::getInstance();
    }
    
    /**
     * Check if a php session has been started.
     *
     * @return bool
     */
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
    
    /**
     * Get configuration absolute path.
     * this absolute path is used to find resources path variables absolute path
     *
     * @return string
     */
    private function getConfigRealPath()
    {
        if (false === $this->configLoaded)
            throw new \Zanra\Framework\Exception\LoadConfigFileException(
                sprintf('Please call "%s"', __CLASS__ . "::loadConfig"));
       
        return $this->configRealPath;
    }
    
    /**
     * Load declared filters.
     */
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
    
    /**
     * Load configuration file
     *
     * @param string $config The config file
     */
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
    
    /**
     * Start the framework MVC engine
     */
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
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
  
    /**
     * Get current controller
     *  
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }
  
    /**
     * Get current action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
  
    /**
     * Get params
     *
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
    }
  
    /**
     * Get all routes
     *
     * @return object[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }
  
    /**
     * Get all resources
     *
     * @return object[]
     */
    public function getResources()
    {
        return $this->resources;
    }
  
    /**
     * Get all filters
     *
     * @return object[]
     */
    public function getFilters()
    {
        return $this->filters;
    }
  
    /**
     * Get session
     *
     * @return \Zanra\Framework\Session\Session
     */
    public function getSession()
    {
        return $this->session;
    }
    
    public function getUrl()
    {
        return $this->urlBag->getUrl();
    }
    
    public function getPath()
    {
        return $this->urlBag->getPath();
    }
    
    public function getAssetPath()
    {
        return $this->urlBag->getAssetPath();
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBag->getBaseUrl();
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->urlBag->getBasePath();
    }
    
    /**
     * Generate url
     *
     * @param string $route
     * @param array  $params
     *
     * @return string
     */
    public function url($route, array $params = array())
    {
        return $this->urlBag->getBaseUrl() . $this->router->generateUrl($route, $params);
    }
  
    /**
     * Generate path
     *
     * @param string   $route
     * @param string[] $params
     *
     * @return string
     */
    public function path($route, array $params = array())
    {
        return $this->urlBag->getBasePath() . $this->router->generateUrl($route, $params);
    }
  
    /**
     * Generate asset
     *
     * @param string $path
     *
     * @return string
     */
    public function asset($path)
    {
        return $this->urlBag->getAssetPath() . $path;
    }
  
    /**
     * Render a controller
     *
     * @param string   $controller
     * @param string[] $params
     *
     * @return string
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
            throw new \Zanra\Framework\Exception\ControllerNotFoundException(
                sprintf('"%s" not found', $controller));
    
        // Check Action
        if (!method_exists($controllerClass, "{$action}"))
            throw new \Zanra\Framework\Exception\ControllerActionNotFoundException(
                sprintf('unable to find "%s" in "%s" scope', $action, $controller));
    
        // Method Args
        $methodArgs = array();
    
        $reflexion = new \ReflectionMethod($controller, $action);
        foreach ($reflexion->getParameters() as $p) {
            $methodArgs[$p->getName()] = null;
            if ($p->isOptional()) {
                $methodArgs[$p->getName()] = $p->getDefaultValue();
            } else {
                if (!isset($params[$p->getName()]) && $params[$p->getName()] !== null)
                    throw new \Zanra\Framework\Router\Exception\ControllerActionMissingDefaultParameterException(
                        sprintf("missing '%s:%s' argument '%s' value (because there is no default value or because there is a non optional argument after this one)", 
                        $controller, $action, $p->getName()));
            }
        }
    
        $params = array_merge($methodArgs, $params);
    
        // Call Action
        $callAction = call_user_func_array(array($controllerClass, $action), $params);
        if (!isset($callAction))
            throw new \Zanra\Framework\Exception\ControllerBadReturnResponseException(
                sprintf('"%s:%s" must return a response. null given', $controller, $action));
    
        return $callAction;
    }
  
    /**
     * Render a template
     *
     * @param string   $filename
     * @param string[] $vars
     *
     * @return string
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
            $this->template = new Template($templateDir, $cacheDir);
        }
    
        return $this->template->render($filename, $vars);
    }
  
    /**
     * Translator
     *
     * @param string $message
     * @param string $locale
     *
     * @return string
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
