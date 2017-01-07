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

use Zanra\Framework\UrlBag\Exception\EmptyURLException;
use Zanra\Framework\UrlBag\Exception\BadURLFormatException;

/**
 * Zanra UrlBag
 *
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
     * UrlBag constructor.
     *
     * @param string $customUrl Used to lunch custom url when in cli mode
     *
     * @throws EmptyURLException
     */
    public function __construct()
    {
        $this->url = '';

        if (php_sapi_name() !== 'cli') {

            $isSecure = false;
        
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $isSecure = true;
            } elseif (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' 
                || ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
                $isSecure = true;
            }

            $protocol = $isSecure ? 'https' : 'http';
            $serverPort = ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443 ) ? '' : (":".$_SERVER["SERVER_PORT"]);
            $hostName = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
            $serverName = isset($_SERVER['HTTP_X_FORWARDED_SERVER']) ? $_SERVER['HTTP_X_FORWARDED_SERVER'] : $_SERVER['SERVER_NAME'];
            $requestUri = $_SERVER['REQUEST_URI'];

            $this->url = $protocol . "://" . $serverName . $serverPort . $requestUri;
        }

        $this->initializeBag();
    }

    /**
     * initializeBag
     */
    private function initializeBag()
    {
        $scriptName = '';
        $dirname = '/';
        $rewriteOn = false;

        if (php_sapi_name() !== 'cli') {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $rewriteOn = ! preg_match("#{$scriptName}#", $this->getUrl());
        }

        $parseUrl = parse_url($this->url);

        $info = pathinfo($scriptName);

        $context = (! empty($info['basename']) && php_sapi_name() !== 'cli' && false === $rewriteOn) ? "/{$info['basename']}" : '';

        $scheme = ! empty($parseUrl['scheme']) ? "{$parseUrl['scheme']}://" : '';
        $host = ! empty($parseUrl['host']) ? "{$parseUrl['host']}" : '';
        $port = ! empty($parseUrl['port']) ? ":{$parseUrl['port']}" : '';
        $query = ! empty($parseUrl['query']) ? "?{$parseUrl['query']}" : '';

        $this->path = ! empty($parseUrl['path']) ? "{$parseUrl['path']}{$query}" : '';

        /* Normalize windows and *nix directory name by using "/" */
        /* Ex: windows directory name can be "\" and *nix directory name is "/" */
        /* This changes all "\" to "/" */
        if (! empty($info['dirname'])) {
            $dirname = str_replace("\\", '/', $info['dirname']);
        }

        $this->assetPath = $dirname != '/' ? $dirname . '/' : $dirname;

        $this->basePath = rtrim($this->assetPath, '/') . $context;
        $this->baseUrl = "{$scheme}{$host}{$port}{$this->basePath}";
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getAssetPath()
    {
        return $this->assetPath;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}
