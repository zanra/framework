<?php
namespace Zanra\Framework\TwigExtend;

class Functions extends \Twig_Extension
{
	public function getFunctions()
	{
		return array(
		// new \Twig_SimpleFunction('lipsum', 'generate_lipsum'),
		);
	}
	
	public function getName()
	{
		return 'functions';
	}
}
