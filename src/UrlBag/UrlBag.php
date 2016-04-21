<?php
namespace Zanra\Framework\UrlBag;

use Zanra\Framework\UrlBag\UrlBagInterface;
use Zanra\Framework\UrlBag\Exception\EmptyURLException;

class UrlBag implements UrlBagInterface
{
  private $url;
  private $path;
  private $scheme;
  private $host;
  private $port;
  private $baseUrl;
  private $basePath;
  private $assetPath;
  private $customUrl;
  
  public function __construct($customUrl = null)
  {
    $this->customUrl = $customUrl;
   
    if (null === $this->customUrl && 'cli' === php_sapi_name()) {
      throw new EmptyURLException(sprintf('url can\'t be empty in CLI mode. Please Initialize the constructor'));
    }
    
    $this->initializeUrl();
  }
  
  /**
   *  urlRewriting
   */
  private function urlRewriting()
  {
    return (isset($_SERVER['REDIRECT_URL']) ? true : false);
  }
  
  /**
   *  initializeUrl
   */
  private function initializeUrl()
  {
    $scriptName       = (php_sapi_name() !== 'cli') ? $_SERVER['SCRIPT_NAME'] : '';
    
    $parseUrl         = parse_url($this->getUrl());
    
    $info             = pathinfo($scriptName);
    
    $this->scheme     = !empty($parseUrl['scheme']) ? "{$parseUrl['scheme']}" : '';
    $this->host       = !empty($parseUrl['host']) ? "{$parseUrl['host']}" : '';
    $this->port       = !empty($parseUrl['port']) ? "{$parseUrl['port']}" : '';
    $this->path       = !empty($parseUrl['path']) ? "{$parseUrl['path']}" : '';
    
    $context          = !empty($info['basename']) ? '/'.$info['basename'] : '';
    $this->assetPath  = !empty($info['dirname']) ? $info['dirname'] : '';
    $this->basePath   = !empty($this->assetPath) ? "{$this->assetPath}{$context}" : '';
    $this->baseUrl    = "{$this->scheme}://{$this->host}:{$this->port}{$this->basePath}";
    
    // if php cli or if mod_rewrite On 
    if (php_sapi_name() != 'cli' && false === $this->urlRewriting() && false == preg_match("#^{$this->baseUrl}#", $this->getUrl())) {
      header("location:{$scriptName}/");
    }
  }
  
  /**
   *  getUrl
   */
  public function getUrl()
  {
    if (php_sapi_name() !== 'cli' && null === $this->customUrl) {
      $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? 's' : '';
      $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
      $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
      $port = ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443 ) ? '' : (":".$_SERVER["SERVER_PORT"]);
      
      $this->url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    } else {
      $this->url = $this->customUrl;
    }
    
    return $this->url;
  }
  
  /**
   *  getPath
   */
  public function getPath()
  {
    return $this->path;
  }
  
  /**
   *  getAssetPath
   */
  public function getAssetPath()
  {
    return $this->assetPath;
  }
  
  /**
   *  getBasePath
   */
  public function getBasePath()
  {
    return $this->basePath;
  }
  
  /**
   *  getBaseUrl
   */
  public function getBaseUrl()
  {
    return $this->baseUrl;
  }
  
  /**
   *  Get host
   *  return String
   *  app->getHost()
   */
  public function getHost()
  {
    return $this->host;
  }
  
  /**
   *  Get scheme
   *  return String
   *  app->getScheme()
   */
  public function getScheme()
  {
    return $this->scheme;
  }
  
  /**
   *  Get port
   *  return String
   *  app->getPort()
   */
  public function getPort()
  {
    return $this->port;
  }
}
