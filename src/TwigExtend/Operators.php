<?php
namespace Zanra\Framework\TwigExtend;

class Operators extends \Twig_Extension
{
	public function getOperators()
	{
		return array(
		  array(
		   '!' => array('precedence' => 50, 'class' => 'Twig_Node_Expression_Unary_Not'),
		  ),
		  array(
		   '||' => array('precedence' => 10, 'class' => 'Twig_Node_Expression_Binary_Or', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
		   '&&' => array('precedence' => 15, 'class' => 'Twig_Node_Expression_Binary_And', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
		  ),
		);
	}
	
	public function getName()
	{
		return 'operators';
	}
}
