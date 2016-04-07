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
   *  application context
   */
  public function getContext();
  
  /**
   *  application relative url
   */
  public function getBasePath();
  
  /**
   *  application absolute url
   */
  public function getBaseUrl();
  
  /**
   *  Get host
   *  return String
   *  app->getContextUrl()
   */
  public function getContextUrl();
  
  /**
   *  Get host
   *  return String
   *  app->getHost()
   */
  public function getHost();
  
  /**
   *  Get scheme
   *  return String
   *  app->getScheme()
   */
  public function getScheme();
  
  /**
   *  Get port
   *  return String
   *  app->getPort()
   */
  public function getPort();
}
