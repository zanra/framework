<?php
namespace Zanra\Framework\Exception;

class MethodNotAllowedException extends \Exception
{
	public function __construct( $message = null )
	{
		parent::__construct( $message, 405 );
	}
}
