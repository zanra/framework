<?php
namespace Zanra\Framework\TwigExtend;

use \Twig_SimpleFilter;

class Filters extends \Twig_Extension implements \Twig_ExtensionInterface
{
	public function getFilters()
	{
		return array();
	}
	
	public function getName()
	{
		return 'filters';
	}
}
