<?php
namespace Zanra\Framework\ErrorHandler;

interface ErrorHandlerWrapperInterface
{
	public function wrap($exception, $type);
}
