<?php
namespace Zanra\Framework\Router;

use Zanra\Framework\Router\RouterInterface;
use Zanra\Framework\Router\Exception\InvalidParameterException;
use Zanra\Framework\Router\Exception\RouteNotFoundException;
use Zanra\Framework\Router\Exception\MissingDefaultParameterException;
use Zanra\Framework\UrlBag\UrlBagInterface;

class Router implements RouterInterface
{
  private $routes;
  private $urlBag;
  
  public function __construct(\stdClass $routes, UrlBagInterface $urlBag)
  {
    $this->routes  = $routes;
    $this->urlBag  = $urlBag;
  }
  
  private function getRoutePattern($route)
  {
    return $route->pattern;
  }
  
  private function getRouteController($route)
  {
    return $route->controller;
  }
  
  private function getRouteParams($route)
  {
    $defaults = array();
    
    if(!empty($route->params)) {
      foreach ($route->params as $k => $v) {
        $defaults[$k] = (trim($v) == '') ? null : trim($v);
      }
    }
    
    return $defaults;
  }
  
  private function forceArrayCombine(array $array1, array $array2) 
  {
    $i = 0;
    foreach ($array1 as $index => $val) {
      $array1[$index] = isset($array2[$i])?$array2[$i]:$val;
      $i++;
    }
    return $array1;
  }
  
  private function getDelimiters($pattern)
  {
    $p = preg_split("#\{[^\{]+\}#",$pattern);
    $delimiters = array();
    
    foreach ($p as $delimiter) {
      $delimiters[] = $delimiter; 
    }
    
    return $delimiters;
  }
  
  private function getSlugs($pattern)
  {
    preg_match_all("#{(.*?)}#", $pattern, $matches);
    
    $slugs = array();
    foreach ($matches[1] as $slug) {
      $slugs[$slug] = '';
    }
    
    return $slugs;
  }
  
  private function extractValues($url, $delimiters)
  {
    $flag = 0;
    $vars = array();
    
    $url = preg_replace("#^{$delimiters[0]}#", '', $url);
    for ($i=1; $i < count($delimiters); $i++) {
      
      $delimiter = preg_quote($delimiters[$i]);
      
      // if delimiter is not empty or is the latest
      if (trim($delimiter) != '' || ($i == count($delimiters)-1)) {
        
        $splits = preg_split("#{$delimiter}#", $url, 2);
        $value = $splits[0];
        
        // if we are on last delimiter and is empty
        if (($i == count($delimiters)-1) && trim($delimiter) == '') {
          $value = $url;
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
        
        $url = preg_replace("#^{$value}{$delimiter}#", '', $url);
      } else {
        $flag++;
      }
    }
    
    $vars = array_map(function($value){
      // if $value is false or $value contains "/" return empty
      return ($value===false || preg_match("#/#", $value)) ? '' : $value;
    }, $vars);
    
    return $vars;
  }
  
  private function setSlugDefaultValues($slugs, $defaults, $setAll = true)
  {
    // check available slugs default values
    foreach ($slugs as $key => $value) {
      if (trim($value) == '') {
        if (in_array($key, array_keys($defaults))) {
          $slugs[$key] = $defaults[$key];
        } else {
          throw new MissingDefaultParameterException(sprintf('missing slug "%s" default value', $key));
        }
      }
    }
    
    if($setAll)
       $slugs = array_merge($defaults, $slugs);
    
    return $slugs;
  }
  
  private function buildUrl($delimiters, $values)
  {
    $url = '';
    $values[] = '';
    
    for ($i=0; $i<count($delimiters); $i++) {
      // example url: /xxx/{slug1}/xxx/{slug2}
      // in this example if slug1 is empty return false
      // but slug2 can be empty
      if (isset($delimiters[$i+1]) && $delimiters[$i+1] != '' && $values[$i] == ''){
        return false;
      }
      $url .= "{$delimiters[$i]}{$values[$i]}";
    }
    
    return $url;
  }
  
  public function matchRequest()
  {
    // Search $contextUrl and if not found search contextUrl with "/" to match empty parameter;
    $url         = $this->urlBag->getPath();
    $rootUrl     = $this->urlBag->getBasePath() . '/';
    $testUrls    = array($url);
    
    if($url !== $rootUrl)
      array_push($testUrls, "{$url}/");
      
    foreach ($testUrls as $testUrl) {
      
      foreach ($this->routes as $routename => $route) {
        
        $routePattern = $this->urlBag->getBasePath() . $this->getRoutePattern($route);
        
        if (!preg_match("#/$#", $routePattern) && preg_match("#/$#", $url) && $url != $rootUrl) {
          continue;
        }
        
        $delimiters       = $this->getDelimiters($routePattern);
        $uriValues        = $this->extractValues($testUrl, $delimiters);
        $buildUrl         = $this->buildUrl($delimiters, $uriValues);
        
        if ($buildUrl == $testUrl) {
          
          $defaults       = $this->getRouteParams($route);
          
          $params         = $this->getSlugs($routePattern);
          $params         = $this->forceArrayCombine($params, $uriValues);
          $params         = $this->setSlugDefaultValues($params, $defaults);
          
          $controller     = explode(':', $this->getRouteController($route));
          
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
  
  public function generateUrl($routename, array $params = array())
  {
    $url = null;
    
    if (property_exists($this->routes, $routename)) {
      
      $route        = $this->routes->$routename;
      $routePattern = $this->getRoutePattern($route);
      $delimiters   = $this->getDelimiters($routePattern);
      $slugs        = $this->getSlugs($routePattern);
      
      // check if $params key is defined in pattern
      foreach ($params as $key => $val) {
        if (!in_array($key, array_keys($slugs))) {
          throw new InvalidParameterException(sprintf('parameter "%s" doesn\'t exists in route "%s"', $key, $routename));
        }
      }
      
      $defaults     = $this->getRouteParams($route);
      $slugs        = array_merge($slugs, $params);
      $slugs        = $this->setSlugDefaultValues($slugs, $defaults, false);
      $url          = $this->buildUrl($delimiters, array_values($slugs));
      
      if (!preg_match("#/$#", $routePattern)) {
        $url = preg_replace("#/$#", "", $url);
      }
      
    } else {
      throw new RouteNotFoundException(sprintf('unable to find Route "%s"', $routename));
    }
    
    return $url;
  }
}
