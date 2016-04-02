<?php
namespace Zanra\Framework\TwigExtend;

use Zanra\Framework\Application;

class Globals extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
	public function getGlobals()
	{
		return array(
		  'app' => Application::getInstance()
		);
	}
	
	public function getName()
	{
		return 'globals';
	}
}
