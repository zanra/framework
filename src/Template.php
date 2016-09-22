<?php
namespace Zanra\Framework;

use Zanra\Framework\Exception\TemplateDirectoryNotFoundException;
use Zanra\Framework\Application;

class Template
{
    /**
     * @var \Twig\Loader\Filesystem
     */
    private $loader;
    
    /**
     * @var \Twig\Environment
     */
    private $template;
    
    /**
     * Constructor.
     *
     * @param string $templateDir
     * @param bool|string $cacheDir
     */
    public function __Construct($templateDir, $cacheDir = false)
    {
        if (false === realpath($templateDir))
            throw new TemplateDirectoryNotFoundException(
            sprintf('template directory not found in "%s"', $templateDir)); 
        
        $this->loader     = new \Twig_Loader_Filesystem($templateDir);
        $this->template   = new \Twig_Environment($this->loader, array(
            'cache' => $cacheDir,
            'auto_reload' => true,
            'strict_variables' => true
        ));
        
        $this->template->addGlobal('app', Application::getInstance());
    }
    
    /**
     * Render a twig template.
     *
     * @return html
     */
    public function render($filename, array $vars = array())
    {
        return $this->template->render($filename, $vars);
    }
}
