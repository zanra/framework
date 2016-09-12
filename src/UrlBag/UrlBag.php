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
  
    public function __construct($customUrl = null)
    {
        if (null === $customUrl && 'cli' === php_sapi_name()) {
            throw new EmptyURLException(
                sprintf('url can\'t be empty in CLI mode. Please Initialize the constructor'));
        }
    
        if (php_sapi_name() !== 'cli' && null === $customUrl) {
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? 's' : '';
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $serverPort = ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443 ) ? '' : (":".$_SERVER["SERVER_PORT"]);

            $this->url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $serverPort . $_SERVER['REQUEST_URI'];
        } else {
            $this->url = $customUrl;
        }

        $this->initializeBag();
    }

    /**
     *  initializeBag
     */
    private function initializeBag()
    {
        $scriptName       = '';
        $rewriteOn        = false;

        if(php_sapi_name() !== 'cli') {
            $scriptName     = $_SERVER['SCRIPT_NAME'];
            $rewriteOn      = !preg_match("#{$scriptName}#", $this->getUrl());
        }

        $parseUrl         = parse_url($this->url);

        $info             = pathinfo($scriptName);
    
        $context          = (!empty($info['basename']) && php_sapi_name() !== 'cli' && false === $rewriteOn) ? "/{$info['basename']}" : '';

        $scheme           = !empty($parseUrl['scheme']) ? "{$parseUrl['scheme']}" : '';
        $host             = !empty($parseUrl['host']) ? "{$parseUrl['host']}" : '';
        $port             = !empty($parseUrl['port']) ? ":{$parseUrl['port']}" : '';
        $query            = !empty($parseUrl['query']) ? "?{$parseUrl['query']}" : '';

        $this->path       = !empty($parseUrl['path']) ? "{$parseUrl['path']}{$query}" : '';
        $this->assetPath  = (!empty($info['dirname']) && $info['dirname'] != '/') ? $info['dirname'] . '/' : '/';

        $this->basePath   = rtrim($this->assetPath, '/').$context;
        $this->baseUrl    = "{$scheme}://{$host}{$port}{$this->basePath}";
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
