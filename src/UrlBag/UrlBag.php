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

        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new BadURLFormatException(
                sprintf('%s not a valid url.', $this->url));
        }

        $this->initializeBag();
    }

    /**
     * initializeBag
     */
    private function initializeBag()
    {
        $scriptName = '';
        $rewriteOn = false;

        if (php_sapi_name() !== 'cli') {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $rewriteOn = !preg_match("#{$scriptName}#", $this->getUrl());
        }

        $parseUrl = parse_url($this->url);

        $info = pathinfo($scriptName);

        $context = (!empty($info['basename']) && php_sapi_name() !== 'cli' && false === $rewriteOn) ? "/{$info['basename']}" : '';

        /* Normalize windows and *nix directory name by using "/" */
        /* ex: windows directory name can be "\" and *nix directory name is "/" */
        $dirname = str_replace("\\", '/', $info['dirname']);

        $scheme = !empty($parseUrl['scheme']) ? "{$parseUrl['scheme']}" : '';
        $host = !empty($parseUrl['host']) ? "{$parseUrl['host']}" : '';
        $port = !empty($parseUrl['port']) ? ":{$parseUrl['port']}" : '';
        $query = !empty($parseUrl['query']) ? "?{$parseUrl['query']}" : '';

        $this->path = !empty($parseUrl['path']) ? "{$parseUrl['path']}{$query}" : '';
        $this->assetPath = (!empty($dirname) && $dirname != '/') ? $dirname . '/' : '/';

        $this->basePath = rtrim($this->assetPath, '/') . $context;
        $this->baseUrl = "{$scheme}://{$host}{$port}{$this->basePath}";
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
