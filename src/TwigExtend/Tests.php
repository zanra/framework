<?php
namespace Zanra\Framework\TwigExtend;

class Tests extends \Twig_Extension
{
	public function getTests()
	{
		return array(
		new \Twig_SimpleTest('even', 'twig_test_even'),
		);
	}
	
	public function getName()
	{
		return 'tests';
	}
}	