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
            $protocol = $this->isSecure() ? 'https' : 'http';
            $serverPort = $this->getServerPort();
            $serverName = $this->getServerName();
            $requestUri = $this->filterServer('REQUEST_URI');

            $this->url = $protocol . "://" . $serverName . $serverPort . $requestUri;
        }

        $this->initializeBag();
    }

    /**
     * Server port filter
     *
     * @return string
     */
    private function getServerPort()
    {
        $serverPort = $this->filterServer('SERVER_PORT');

        if ($serverPort == 80 || $serverPort == 443) {
            $serverPort = '';
        }

        return $serverPort;
    }

    /**
     * Server name filter
     *
     * @return string
     */
    private function getServerName()
    {
        $serverName = $this->filterServer('SERVER_NAME');

        if ($this->filterServer('HTTP_X_FORWARDED_SERVER') != null) {
            $serverName = $this->filterServer('HTTP_X_FORWARDED_SERVER');
        }

        return $serverName;
    }

    /**
     * Avoid directly access in $_SERVER
     *
     * @param string $name
     *
     * @return mixed
     */
    private function filterServer($name)
    {
        return filter_input(INPUT_SERVER, $name);
    }

    /**
     * Check if url is secured
     */
    private function isSecure()
    {
        $isSecure = false;

        $serverHTTPS = '';
        $serverForwadedProto = '';
        $serverForwadedSSL = '';

        // HTTPS
        if ($this->filterServer('HTTPS') !== null) {
            $serverHTTPS = strtolower($this->filterServer('HTTPS'));
        }

        // HTTP_X_FORWARDED_PROTO
        if ($this->filterServer('HTTP_X_FORWARDED_PROTO') !== null) {
            $serverForwadedProto = strtolower($this->filterServer('HTTP_X_FORWARDED_PROTO'));
        }

        // HTTP_X_FORWARDED_SSL
        if ($this->filterServer('HTTP_X_FORWARDED_SSL') !== null) {
            $serverForwadedSSL = strtolower($this->filterServer('HTTP_X_FORWARDED_SSL'));
        }

        if ($serverHTTPS == 'on' || $serverForwadedProto == 'https' || $serverForwadedSSL == 'on') {
            $isSecure = true;
        }

        return $isSecure;
    }

    /* Normalize windows and *nix directory name by using "/"
     * Ex: windows directory name can be "\" and *nix directory name is "/"
     * This changes all "\" to "/"
     */
    private function dirnameNormaliser($directoryName)
    {
        $dirname = '/';

        if (! empty($directoryName)) {
            $dirname = str_replace("\\", '/', $directoryName);
            $dirname = ($dirname != '/') ? $dirname . '/' : $dirname;
        }

        return $dirname;
    }

    /**
     * Check if rewrite engine is on
     *
     * @return bool
     */
    private function isRewrite()
    {
        $rewrite = false;

        if (php_sapi_name() !== 'cli') {
            $rewrite = ! preg_match("#{$this->filterServer('SCRIPT_NAME')}#", $this->getUrl());
        }

        return $rewrite;
    }

    /**
     * parse_url array filter
     *
     * @param $url
     *
     * @return array
     */
    private function parseUrl($url)
    {
        $parseUrl = parse_url($url);

        if ($parseUrl === false) {
            throw new BadURLFormatException(sprintf('Malformed URL "%s"', $url));
        }

        $parser = array(
          'scheme'  => '',
          'host'    => '',
          'port'    => '',
          'query'   => '',
          'path'    => ''
        );

        if (! empty($parseUrl['scheme'])) {
            $parser['scheme'] = "{$parseUrl['scheme']}://";
        }

        if (! empty($parseUrl['host'])) {
            $parser['host'] = $parseUrl['host'];
        }

        if (! empty($parseUrl['port'])) {
            $parser['port'] = ":{$parseUrl['port']}";
        }

        if (! empty($parseUrl['query'])) {
            $parser['query'] = $parseUrl['query'];
        }

        if (! empty($parseUrl['path'])) {
            $parser['path'] = $parseUrl['path'];
        }

        return $parser;
    }

    /**
     * path_info array filter
     *
     * @param $scriptName
     *
     * @return array
     */
    private function parsePathInfo($scriptName)
    {
        $info = pathinfo($scriptName);

        $parser['context'] = (! empty($info['basename']) && false === $this->isRewrite()) ? "/{$info['basename']}" : '';
        $parser['dirname'] = $this->dirnameNormaliser($info['dirname']);

        return $parser;
    }

    /**
     * initializeBag
     */
    private function initializeBag()
    {
        if (php_sapi_name() !== 'cli') {
            $parsedPathInfo = $this->parsePathInfo($this->filterServer('SCRIPT_NAME'));
            $parsedUrl = $this->parseUrl($this->getUrl());

            $this->assetPath = $parsedPathInfo['dirname'];
            $this->path = ! empty($parsedUrl['path']) ? "{$parsedUrl['path']}{$parsedUrl['query']}" : '';
            $this->basePath = rtrim($this->assetPath, '/') . $parsedPathInfo['context'];
            $this->baseUrl = "{$parsedUrl['scheme']}{$parsedUrl['host']}{$parsedUrl['port']}{$this->basePath}";
        }
    }

    /**
     * @return string
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
