<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Router;

use Zanra\Framework\Router\Exception\InvalidParameterException;
use Zanra\Framework\Router\Exception\RouteNotFoundException;
use Zanra\Framework\Router\Exception\MissingDefaultParameterException;
use Zanra\Framework\UrlBag\UrlBagInterface;

/**
 * Zanra Router
 *
 * @author Targalis
 *
 */
class Router implements RouterInterface
{
    /**
     * @var RouterInterface
     */
    private $routes;

    /**
     * @var UrlBagInterface
     */
    private $urlBag;

    /**
     * Constructor
     *
     * @param RouterInterface $routes
     */
    public function __construct(\stdClass $routes)
    {
        $this->routes  = $routes;
    }

    /**
     * @param stdClass $route
     *
     * @return string
     */
    private function getRoutePattern($route)
    {
        return $route->pattern;
    }

    /**
     * @param stdClass $route
     *
     * @return string
     */
    private function getRouteController($route)
    {
        return $route->controller;
    }

    /**
     * @param stdClass $route
     *
     * @return array
     */
    private function getRouteParams($route)
    {
        $defaults = array();

        if (! empty($route->params)) {
            foreach ($route->params as $k => $v) {
                $defaults[$k] = (trim($v) == '') ? null : trim($v);
            }
        }

        return $defaults;
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function forceArrayCombine(array $array1, array $array2)
    {
        $i = 0;
        foreach ($array1 as $index => $val) {
            $array1[$index] = isset($array2[$i]) ? $array2[$i] : $val;
            $i++;
        }

        return $array1;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    private function getDelimiters($pattern)
    {
        $p = preg_split("#\{[^\{]+\}#",$pattern);
        $delimiters = array();

        foreach ($p as $delimiter) {
            $delimiters[] = $delimiter;
        }

        return $delimiters;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    private function getSlugs($pattern)
    {
        preg_match_all("#{(.*?)}#", $pattern, $matches);

        $slugs = array();
        foreach ($matches[1] as $slug) {
            $slugs[$slug] = '';
        }

        return $slugs;
    }

    /**
     * @param string $uri
     * @param array $delimiters
     *
     * @return array
     */
    private function extractValues($uri, $delimiters)
    {
        $flag = 0;
        $vars = array();

        $delimiter = preg_quote($delimiters[0]);

        $uri = preg_replace("#^{$delimiter}#", '', $uri);
        for ($i = 1; $i < count($delimiters); $i++) {

            $delimiter = preg_quote($delimiters[$i]);

            // if delimiter is not empty or is the latest
            if (trim($delimiter) != '' || ($i == count($delimiters)-1)) {

                $splits = preg_split("#{$delimiter}#", $uri, 2);
                $value = $splits[0];

                // if we are on last delimiter and is empty
                if (($i == count($delimiters)-1) && trim($delimiter) == '') {
                    $value = $uri;
                }

                // if temp flag is not empty
                if ($flag > 0) {
                    // forced assignment
                    $len = ((strlen($value)-$flag) < 1) ? 1 : strlen($value) - $flag;
                    $temp = array();
                    $temp[] = substr($value, 0, $len);
                    for($t = 0; $t < $flag; $t++ ) {
                        $temp[] = substr($value, $len + $t, 1);
                    }
                    $vars = array_merge($vars,$temp);
                    $flag = 0;
                } else {
                    // default assignment
                    $vars[] = $value;
                }

                $valueQuoted = preg_quote($value);
                $uri = preg_replace("#^{$valueQuoted}{$delimiter}#", '', $uri);
            } else {
                $flag++;
            }
        }

        $vars = array_map(function($value) {
            // if $value is false or $value contains "/" return empty
            return ($value === false || preg_match("#/#", $value)) ? '' : $value;
        }, $vars);

        return $vars;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function encodeParams(array $params = array()) {

        foreach ($params as $key => $param) {
            $params[$key] = urlencode($param);
        }

        return $params;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function decodeParams(array $params = array()) {

        foreach ($params as $key => $param) {
            $params[$key] = urldecode($param);
        }

        return $params;
    }

    /**
     * @param array $slugs
     * @param array $defaults
     * @param bool $setAll
     *
     * @throws MissingDefaultParameterException
     *
     * @return array
     */
    private function setSlugDefaultValues($slugs, $defaults, $setAll = true)
    {
        // check availables slugs default values
        foreach ($slugs as $key => $value) {
            if (trim($value) == '') {
                if (! in_array($key, array_keys($defaults))) {
                    throw new MissingDefaultParameterException(
                        sprintf('missing slug "%s" default value', $key));
                }

                $slugs[$key] = $defaults[$key];
            }
        }

        if ($setAll) {
            $slugs = array_merge($defaults, $slugs);
        }

        return $slugs;
    }

    /**
     * @param $delimiters
     * @param $values
     *
     * @return bool|string
     */
    private function buildUri($delimiters, $values)
    {
        $uri = '';
        $values[] = '';

        for ($i = 0; $i < count($delimiters); $i++) {
            // example url: /xxx/{slug1}/xxx/{slug2}
            // in this example if slug1 is empty return false
            // but slug2 can be empty
            if (isset($delimiters[$i+1]) && $delimiters[$i+1] != '' && $values[$i] == '') {
                return false;
            }

            $uri .= "{$delimiters[$i]}{$values[$i]}";
        }

        return $uri;
    }

    /**
     * @return string
     */
    private function getUriWithoutQueryString($uri)
    {
        $uriWithoutQuery = strstr($uri, '?', true);
        $uri = ($uriWithoutQuery === false) ? $uri : $uriWithoutQuery;

        return $uri;
    }

    /**
     * @param UrlBagInterface $urlBag
     *
     * @return array|bool
     */
    public function matchRequest(UrlBagInterface $urlBag)
    {
        $uri = $this->getUriWithoutQueryString($urlBag->getPath());

        $rootUri = $urlBag->getBasePath() . '/';

        // Search $contextUrl and if not found
        // search contextUrl with "/" to match
        // empty parameters;

        $testUris = array($uri);
        if ($uri !== $rootUri) {
            array_push($testUris, "{$uri}/");
        }

        foreach ($testUris as $testUri) {

            foreach ($this->routes as $routename => $route) {

                $routePattern = $urlBag->getBasePath() .$this->getRoutePattern($route);

                if (! preg_match("#/$#", $routePattern) && preg_match("#/$#", $uri) && $uri != $rootUri) {
                    continue;
                }

                $delimiters = $this->getDelimiters($routePattern);
                $uriParams = $this->extractValues($testUri, $delimiters);
                $buildUri = $this->buildUri($delimiters, $uriParams);

                if ($buildUri == $testUri) {

                    $defaults = $this->getRouteParams($route);

                    $uriParams = $this->decodeParams($uriParams);

                    $params = $this->getSlugs($routePattern);
                    $params = $this->forceArrayCombine($params, $uriParams);
                    $params = $this->setSlugDefaultValues($params, $defaults);

                    $controller = explode(':', $this->getRouteController($route));

                    return array(
                        "route"       => $routename,
                        "controller"  => $controller[0],
                        "action"      => $controller[1],
                        "params"      => $params
                    );
                }
            }
        }

        return false;
    }

    /**
     * @param string $routename
     * @param array $params
     *
     * @return string
     */
    public function generateUri($routename, array $params = array())
    {
        if (! property_exists($this->routes, $routename)) {
            throw new RouteNotFoundException(
                sprintf('unable to find Route "%s"', $routename));
        }

        $route = $this->routes->$routename;
        $routePattern = $this->getRoutePattern($route);
        $delimiters = $this->getDelimiters($routePattern);
        $slugs = $this->getSlugs($routePattern);

        // check if $params key is defined in pattern
        foreach ($params as $key => $val) {
            if (! in_array($key, array_keys($slugs))) {
                throw new InvalidParameterException(
                   sprintf('parameter "%s" doesn\'t exists in route "%s"', $key, $routename));
            }
        }

        $defaults = $this->getRouteParams($route);

        $params = $this->encodeParams($params);

        $slugs = array_merge($slugs, $params);
        $slugs = $this->setSlugDefaultValues($slugs, $defaults, false);
        $uri = $this->buildUri($delimiters, array_values($slugs));


        if (! preg_match("#/$#", $routePattern)) {
            $uri = preg_replace("#/$#", "", $uri);
        }

        return $uri;
    }
}
