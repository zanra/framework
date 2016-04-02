<?php
namespace Zanra\Framework;

use Zanra\Framework\Exception\TemplateDirectoryNotFoundException;

class Template
{
  private $loader;
  private $template;
  
  private static $_instance = null;
  
  public function __Construct($templateDir, $cacheDir = false)
  {
    if (false === realpath($templateDir))
        throw new TemplateDirectoryNotFoundException(sprintf('template directory not found in "%s"', $templateDir)); 
        
    $this->loader     = new \Twig_Loader_Filesystem($templateDir);
    $this->template   = new \Twig_Environment($this->loader, array(
        'cache' => $cacheDir,
        'auto_reload' => true,
        'strict_variables' => true
    ));
    
    // Extension
    $this->template->addExtension(new \Zanra\Framework\TwigExtend\Globals());
    $this->template->addExtension(new \Zanra\Framework\TwigExtend\Filters());
    $this->template->addExtension(new \Zanra\Framework\TwigExtend\Functions());
    $this->template->addExtension(new \Zanra\Framework\TwigExtend\Operators());
  }
  
  public function render($filename, array $vars = array())
  {
    return $this->template->render($filename, $vars);
  }
}
