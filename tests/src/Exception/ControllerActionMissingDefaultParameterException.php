<?php
namespace Zanra\Framework\Router\Exception;

class ControllerActionMissingDefaultParameterException extends \Exception
{
	public function __construct($message = null)
	{
		parent::__construct($message, 404);
	}
}
