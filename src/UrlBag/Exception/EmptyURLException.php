<?php
namespace Zanra\Framework\UrlBag\Exception;

class EmptyURLException extends \ErrorException
{
	public function __construct($message = null)
	{
		parent::__construct($message, 404);
	}
}		