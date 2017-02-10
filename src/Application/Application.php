<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Application;

use Zanra\Framework\UrlBag\UrlBag;
use Zanra\Framework\Router\Router;
use Zanra\Framework\Router\Exception\RouteNotFoundException;
use Zanra\Framework\Session\Session;
use Zanra\Framework\Template\Template;
use Zanra\Framework\Template\TemplateInterface;
use Zanra\Framework\FileLoader\FileLoader;
use Zanra\Framework\Translator\Translator;
use Zanra\Framework\Application\Exception\LoadConfigFileException;
use Zanra\Framework\Application\Exception\FilterNotFoundException;
use Zanra\Framework\Application\Exception\FilterMethodNotFoundException;
use Zanra\Framework\Application\Exception\FilterBadFormatException;
use Zanra\Framework\Application\Exception\RouteBadFormatException;
use Zanra\Framework\Application\Exception\ResourceKeyNotFoundException;
use Zanra\Framework\Application\Exception\ControllerNotFoundException;
use Zanra\Framework\Application\Exception\ControllerActionNotFoundException;
use Zanra\Framework\Application\Exception\ControllerActionMissingDefaultParameterException;
use Zanra\Framework\Application\Exception\ControllerBadReturnResponseException;
use Zanra\Framework\ErrorHandler\ErrorHandler;
use Zanra\Framework\ErrorHandler\ErrorHandlerWrapperInterface;

/**
 * Zanra Application
 *
 * @author Targalis
 */
class Application
{
    const IMPORT_SECTION = "import";
    const APPLICATION_SECTION = "application";
    const LOCALE_KEY = "default.locale";
    const ROUTING_KEY = "routing.file";
    const FILTERS_KEY = "filters.file";
    const TRANSLATION_KEY = "translation.dir";
    const TEMPLATE_KEY = "template.dir";
    const CACHE_KEY = "cache.dir";
    const LOGS_KEY = "logs.dir";
    const SESSION_LOCALE_KEY = "_locale";
    const FILTER_BEFORE = "before";
    const FILTER_AFTER = "after";

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $logDir;

    /**
     * @var string
     */
    private $translationDir;

    /**
     * @var string
     */
    private $templateDir;

    /**
     * @var string
     */
    private $defaultLocale;

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
     * @var UrlBag
     */
    private $urlBag;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var FileLoader
     */
    private $fileLoader;

    /**
     * @var string
     */
    private $configRealPath = null;

    /**
     * @var string
     */
    private $routesFile = null;

    /**
     * @var string
     */
    private $filtersFile = null;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var bool
     */
    private $configLoaded  = false;

    /**
     * @var Application
     */
    private static $instance = null;

    /**
     * Application constructor.
     */
    protected function __construct()
    {
        $this->urlBag     = new UrlBag();
        $this->session    = new Session();
        $this->fileLoader = new FileLoader();
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     */
    private function __clone()
    {
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
     * Check if given $path is relative
     *
     * @param string $path
     *
     * @return bool
     */
    private function isRelativePath($path)
    {
        $isRelative = false;

        if (! preg_match("#^/|[a-zA-Z]:(\\\|/)#", $path)) {
            $isRelative = true;
        }

        return $isRelative;
    }

    /**
     * Load declared filters.
     *
     * @param string $execute [before, after]
     *
     * @throws FilterMethodNotFoundException
     * @throws FilterNotFoundException
     */
    private function loadFilters($execute)
    {
        foreach ($this->getFilters() as $filter => $filterExecute) {

            $filterExecute = trim($filterExecute);

            if (empty($filterExecute) || ! in_array($filterExecute, array(self::FILTER_BEFORE, self::FILTER_AFTER))) {
                throw new FilterBadFormatException(
                    sprintf('Filters declaration bad well formed in %s. Only value "%s" and "%s" allowed. Called', $this->filtersFile, self::FILTER_BEFORE, self::FILTER_AFTER)
                );
            }

            if ($filterExecute != $execute) {
                continue;
            }

            $part = explode('.', $filter);

            if (empty($part[0]) || empty($part[1])) {
                throw new FilterBadFormatException(
                    sprintf('Filters declaration bad well formed. For a ClassFilter use Class.Method in %s', $this->filtersFile)
                );
            }

            $class = $part[0];
            $method = $part[1];

            $filterNamespaceClass = "\\Filter\\{$class}Filter";
            $filterClass = class_exists($filterNamespaceClass) ? new $filterNamespaceClass() : null;

            if (null === $filterClass) {
                throw new FilterNotFoundException(
                    sprintf('Class "%s" not found', $filterNamespaceClass)
                );
            }

            if (! method_exists($filterClass, $method)) {
                throw new FilterMethodNotFoundException(
                    sprintf(
                        'Unable to find Method "%s" for "%s" Class, defined in %s and called',
                        $method,
                        $filterNamespaceClass,
                        $this->filtersFile
                    )
                );
            }

            call_user_func_array(array($filterClass, $method), array($this));
        }
    }

    /**
     * Load configuration file
     *
     * @param string $configFile The config file
     *
     * @throws ResourceKeyNotFoundException
     */
    public function loadConfig($configFile)
    {
        if (false !== $this->configLoaded) {
            return;
        }

        $this->configLoaded   = true;
        $this->configRealPath = realpath(dirname($configFile));
        $this->resources      = $this->fileLoader->load($configFile);

        // Import
        if (! empty($this->resources->{self::IMPORT_SECTION})) {
            $import = $this->resources->{self::IMPORT_SECTION};

            foreach ($import as $key => $path) {
                $path = empty($path) ? null : $path;

                if ($path != null && $this->isRelativePath($path)) {
                    $path = $this->configRealPath . DIRECTORY_SEPARATOR . $path;
                }

                $this->resources->{self::IMPORT_SECTION}->$key  = $this->fileLoader->load($path);
            }
        }

        // Application
        if (! isset($this->resources->{self::APPLICATION_SECTION})) {
            throw new ResourceKeyNotFoundException(
                sprintf('section key "[%s]" not declared in resources', self::APPLICATION_SECTION)
            );
        }

        // Cache directory
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::CACHE_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('key "%s" not declared in resources [%s] section', self::CACHE_KEY, self::APPLICATION_SECTION)
            );
        }

        $cacheDirKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::CACHE_KEY});
        $this->cacheDir = empty($cacheDirKey) ? null : $cacheDirKey;

        if ($this->cacheDir != null && $this->isRelativePath($this->cacheDir)) {
            $this->cacheDir = $this->configRealPath . DIRECTORY_SEPARATOR . $cacheDirKey;
        }

        // Logs directory
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::LOGS_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('key "%s" not declared in resources [%s] section', self::LOGS_KEY, self::APPLICATION_SECTION)
            );
        }

        $logDirKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::LOGS_KEY});
        $this->logDir = empty($logDirKey) ? null : $logDirKey;

        if ($this->logDir != null && $this->isRelativePath($this->logDir)) {
            $this->logDir = $this->configRealPath . DIRECTORY_SEPARATOR . $logDirKey;
        }

        // Transation directory
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::TRANSLATION_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('resource key "%s" not declared in resources [%s] section', self::TRANSLATION_KEY, self::APPLICATION_SECTION)
            );
        }

        $translationDirKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::TRANSLATION_KEY});
        $this->translationDir = empty($translationDirKey) ? null : $translationDirKey;

        if ($this->translationDir != null && $this->isRelativePath($this->translationDir)) {
            $this->translationDir = $this->configRealPath . DIRECTORY_SEPARATOR . $translationDirKey;
        }

        // Routes file
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::ROUTING_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('key "%s" not declared in resources [%s] section', self::ROUTING_KEY, self::APPLICATION_SECTION)
            );
        }

        $routeFileKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::ROUTING_KEY});
        $this->routesFile = empty($routeFileKey) ? null : $routeFileKey;

        if ($this->routesFile != null && $this->isRelativePath($this->routesFile)) {
            $this->routesFile = $this->configRealPath . DIRECTORY_SEPARATOR . $routeFileKey;
        }

        $this->routes = $this->fileLoader->load($this->routesFile);

        // Filters file
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::FILTERS_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('key "%s" not declared in resources [%s] section', self::FILTERS_KEY, self::APPLICATION_SECTION)
            );
        }

        $filterFileKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::FILTERS_KEY});
        $this->filtersFile = empty($filterFileKey) ? null : $filterFileKey;

        if ($this->filtersFile != null && $this->isRelativePath($this->filtersFile)) {
            $this->filtersFile = $this->configRealPath . DIRECTORY_SEPARATOR . $filterFileKey;
        }

        $this->filters = $this->fileLoader->load($this->filtersFile);

        // Template directory
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::TEMPLATE_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('key "%s" not declared in resources [%s] section', self::TEMPLATE_KEY, self::APPLICATION_SECTION)
            );
        }

        $templateDirKey = trim($this->resources->{self::APPLICATION_SECTION}->{self::TEMPLATE_KEY});
        $this->templateDir = empty($templateDirKey) ? null : $templateDirKey;

        if ($this->templateDir != null && $this->isRelativePath($this->templateDir)) {
            $this->templateDir = $this->configRealPath . DIRECTORY_SEPARATOR . $templateDirKey;
        }

        // Default Locale
        if (! isset($this->resources->{self::APPLICATION_SECTION}->{self::LOCALE_KEY})) {
            throw new ResourceKeyNotFoundException(
                sprintf('resource key "%s" not declared in resources [%s] section', self::LOCALE_KEY, self::APPLICATION_SECTION)
            );
        }

        $this->defaultLocale = trim($this->resources->{self::APPLICATION_SECTION}->{self::LOCALE_KEY});
    }

    /**
     * Start the framework MVC engine
     *
     * @param ErrorHandlerWrapperInterface $errorHandlerWrapper
     *
     * @throws LoadConfigFileException
     * @throws RouteNotFoundException
     */
    public function mvcHandle(ErrorHandlerWrapperInterface $errorHandlerWrapper)
    {
        if (null !== $this->router) {
            return;
        }

        ErrorHandler::init($errorHandlerWrapper, $this->logDir);

        if (false === $this->configLoaded) {
            throw new LoadConfigFileException(
                sprintf('Please call "%s" before call "%s"', __CLASS__ . "::loadConfig", __METHOD__)
            );
        }

        $this->router = new Router($this->getRoutes());

        // match current request
        if (false === $matches = $this->router->matchRequest($this->urlBag)) {
            throw new RouteNotFoundException(
                sprintf('No route found for "%s"', $this->getUrl())
            );
        }

        if (empty($matches["controller"]) || empty($matches["action"])) {
            throw new RouteBadFormatException(
                sprintf(
                    'Routing declaration bad well formed. For a ClassController use Class:Method in %s',
                    $this->routesFile
                )
            );
        }

        $this->route      = $matches["route"];
        $this->controller = $matches["controller"];
        $this->action     = $matches["action"];
        $this->params     = $matches["params"];

        // before filters
        $this->loadFilters(self::FILTER_BEFORE);

        // call controller
        print($this->renderController("{$this->getController()}:{$this->getAction()}", $this->getParams()));

        // after filters
        $this->loadFilters(self::FILTER_AFTER);
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Get log directory
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * Get template directory
     *
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * Get translation directory
     *
     * @return string
     */
    public function getTranslationDir()
    {
        return $this->translationDir;
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
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set template
     *
     * @param TemplateInterface $template
     */
    public function setTemplate(TemplateInterface $template)
    {
        $this->template = $template;
    }

    /**
     * Get template
     *
     * @return Template
     */
    public function getTemplate()
    {
        if (null === $this->template) {
            $this->template = new Template($this);
        }

        return $this->template;
    }

    /**
     * Get url
     *
     * @return null|string
     */
    public function getUrl()
    {
        return $this->urlBag->getUrl();
    }

    /**
     * Get path
     *
     * @return null|string
     */
    public function getPath()
    {
        return $this->urlBag->getPath();
    }

    /**
     * Get asset path
     *
     * @return null|string
     */
    public function getAssetPath()
    {
        return $this->urlBag->getAssetPath();
    }

    /**
     * Get base url
     *
     * @return null|string
     */
    public function getBaseUrl()
    {
        return $this->urlBag->getBaseUrl();
    }

    /**
     * Get base path
     *
     * @return null|string
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
     * @return null|string
     */
    public function url($route, array $params = array())
    {
        return $this->urlBag->getBaseUrl() . $this->router->generateUri($route, $params);
    }

    /**
     * Generate path
     *
     * @param string   $route
     * @param string[] $params
     *
     * @return null|string
     */
    public function path($route, array $params = array())
    {
        return $this->urlBag->getBasePath() . $this->router->generateUri($route, $params);
    }

    /**
     * Generate asset

     * @param string $path
     *
     * @return null|string
     */
    public function asset($path)
    {
        return $this->urlBag->getAssetPath() . $path;
    }

    /**
     * Render a controller
     *
     * @param string $controller
     * @param array  $params
     *
     * @return mixed
     *
     * @throws ControllerActionMissingDefaultParameterException
     * @throws ControllerActionNotFoundException
     * @throws ControllerBadReturnResponseException
     * @throws ControllerNotFoundException
     */
    public function renderController($controller, array $params = array())
    {
        $parts = explode(':', $controller);

        $controller = "\\Controller\\{$parts[0]}Controller";
        $action = "{$parts[1]}Action";

        // Check Controller
        if (! class_exists($controller)) {
            throw new ControllerNotFoundException(
                sprintf('"%s" not found', $controller)
            );
        }

        $controllerClass = new $controller();

        // Check Action
        if (! method_exists($controllerClass, "{$action}")) {
            throw new ControllerActionNotFoundException(
                sprintf('unable to find "%s" in "%s" scope', $action, $controller)
            );
        }

        // Method Args
        $methodArgs = array();

        $reflexion = new \ReflectionMethod($controller, $action);

        foreach ($reflexion->getParameters() as $p) {
            if (! $p->isOptional() && ! isset($params[$p->getName()]) && $params[$p->getName()] !== null) {
                throw new ControllerActionMissingDefaultParameterException(
                    sprintf(
                        "missing '%s:%s' argument '%s' value (because there is no default value or
                        because there is a non optional argument after this one)",
                        $controller,
                        $action,
                        $p->getName()
                    )
                );
            }

            $methodArgs[$p->getName()] = null;

            if ($p->isOptional()) {
                $methodArgs[$p->getName()] = $p->getDefaultValue();
            }
        }

        $params = array_merge($methodArgs, $params);

        // Call Action
        $callAction = call_user_func_array(array($controllerClass, $action), $params);

        if (! isset($callAction)) {
            throw new ControllerBadReturnResponseException(
                sprintf('"%s:%s" must return a response. null given', $controller, $action)
            );
        }

        return $callAction;
    }

    /**
     * Render a template
     *
     * @param string $filename
     * @param array  $vars
     *
     * @return string
     */
    public function renderView($filename, array $vars = array())
    {
        return $this->getTemplate()->render($filename, $vars);
    }

    /**
     * Translator
     *
     * @param string $message
     * @param mixed  $params|$locale
     *
     * @return string
     */
    public function translate($message)
    {
        if (null == $this->translator) {
            $this->translator = new Translator($this->fileLoader);
            $this->translator->setTranslationDir($this->translationDir);
        }

        $args = func_get_args();

        $params = array();
        $locale = null;

        $message = array_shift($args);

        $argv = count($args);

        if ($argv > 2) {
            trigger_error(__METHOD__ ." expects at least 3 parameters, {$argv} given", E_USER_WARNING);
        }

        $firstArg = isset($args[0]) ? $args[0] : null;
        $secondArg = isset($args[1]) ? $args[1] : null;

        if ($firstArg !== null) {
            $gettype = gettype($firstArg);

            if ($gettype != 'array' && ! empty($secondArg)) {
                trigger_error(__METHOD__ ." expects parameter 2 to be array, {$gettype} given", E_USER_WARNING);
            }

            if (is_array($firstArg)) {
                $params = $firstArg;
            } else {
                $locale = $firstArg;
            }
        }

        if ($locale === null && $secondArg !== null) {
            $gettype = gettype($secondArg);

            if ($gettype != 'string') {
                trigger_error(__METHOD__ ." expects parameter 1 to be string, {$gettype} given", E_USER_WARNING);
            }

            $locale = $secondArg;
        }

        if ($locale == null) {
            if (! $this->hasSession()) {
                $locale = $this->defaultLocale;
            } else {
                $sessionLocale = $this->getSession()->get(self::SESSION_LOCALE_KEY);
                $locale = ! empty($sessionLocale) ? $sessionLocale : $this->defaultLocale;
            }
        }

        return $this->translator->translate($message, $params, $locale);
    }

    /**
     * Singleton
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Application();
        }

        return self::$instance;
    }
}
