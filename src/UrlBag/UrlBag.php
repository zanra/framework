<?php
namespace Zanra\Framework\UrlBag;

use Zanra\Framework\UrlBag\UrlBagInterface;
use Zanra\Framework\UrlBag\Exception\EmptyURLException;

class UrlBag implements UrlBagInterface
{
  private $url;
  private $path;
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
    return !isset($_SERVER['BASE'])&&!isset($_SERVER['PATH_TRANSLATED'])&&isset($_SERVER['REDIRECT_BASE']) ? true : false;
  }

  /**
   *  initializeUrl
   */
  private function initializeUrl()
  {
    $scriptName       = (php_sapi_name() !== 'cli') ? $_SERVER['SCRIPT_NAME'] : '';

    if (php_sapi_name() !== 'cli' && null === $this->customUrl) {
      $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? 's' : '';
      $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
      $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
      $serverPort = ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443 ) ? '' : (":".$_SERVER["SERVER_PORT"]);

      $this->url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $serverPort . $_SERVER['REQUEST_URI'];
    } else {
      $this->url = $this->customUrl;
    }

    $parseUrl         = parse_url($this->url);

    $info             = pathinfo($scriptName);

    $context          = (!empty($info['basename']) && php_sapi_name() !== 'cli' && false === $this->urlRewriting()) ? "/{$info['basename']}" : '';

    $scheme           = !empty($parseUrl['scheme']) ? "{$parseUrl['scheme']}" : '';
    $host             = !empty($parseUrl['host']) ? "{$parseUrl['host']}" : '';
    $port             = !empty($parseUrl['port']) ? ":{$parseUrl['port']}" : '';

    $this->path       = !empty($parseUrl['path']) ? "{$parseUrl['path']}" : '';
    $this->assetPath  = (!empty($info['dirname']) && $info['dirname'] != '/') ? $info['dirname'] . '/' : '/';

    $this->basePath   = rtrim($this->assetPath, '/').$context;
    $this->baseUrl    = "{$scheme}://{$host}{$port}{$this->basePath}";

    // if php cli or if mod_rewrite On 
    if (php_sapi_name() !== 'cli' && false === $this->urlRewriting() && false == preg_match("#^{$this->baseUrl}#", $this->getUrl())) {
      header("location:{$scriptName}/");
      exit();
    }
  }

  /**
   *  getUrl
   */
  public function getUrl()
  {
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
   *  getBaseUrl
   */
  public function getBaseUrl()
  {
    return $this->baseUrl;
  }

  /**
   * getBasePath
   */
  public function getBasePath()
  {
   return $this->basePath;
  }
}
