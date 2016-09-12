<?php
namespace Zanra\Framework\UrlBag;

interface UrlBagInterface
{
    /**
     *  getUrl
     */
    public function getUrl();
  
    /**
     *  getPath
     */
    public function getPath();
  
    /**
     *  getAssetPath
     */
    public function getAssetPath();
  
    /**
     *  getBaseUrl
     */
    public function getBaseUrl();

    /**
     * getBasePath
     */
    public function getBasePath();
}
