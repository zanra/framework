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
   *  application absolute url
   */
  public function getBaseUrl();
}
