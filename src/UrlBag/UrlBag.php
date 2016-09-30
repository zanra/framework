<?php
    
/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Zanra\Framework\UrlBag;

use Zanra\Framework\UrlBag\UrlBagInterface;
use Zanra\Framework\UrlBag\Exception\EmptyURLException;

/**
 * Zanra urlBag
 * @author Targalis
 *
 */
class UrlBag implements UrlBagInterface
{
    /**
     * @var string
     */
    private $url;
    
    /**
     * @var string
     */
    private $path;
    
    /**
     * @var string
     */
    private $baseUrl;
    
    /**
     * @var string
     */
    private $basePath;
    
    /**
     * @var string
     */
    private $assetPath;
    
    /**
     * Constructor.
     * @param string $customUrl Used to lunch custom url when in cli mode 
     */
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
     * initializeBag
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
     * Get the current full url
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Get the current path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Get asset path
     * @return string
     */
    public function getAssetPath()
    {
        return $this->assetPath;
    }
    
    /**
     * Get the current base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    
    /**
     * Get the current base path
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}
