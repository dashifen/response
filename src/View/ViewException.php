<?php

namespace Dashifen\Response\View;

use Throwable;

class ViewException extends \Exception {
	public const AFTER_COMPILE_ALTERATION = 1;
	public const RECOMPILATION = 2;
	public const UNKNOWN_ERROR = 3;
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$reflection = new \ReflectionClass(__CLASS__);
		if (!in_array($code, $reflection->getConstants())) {
			$code = self::UNKNOWN_ERROR;
		}
		
		parent::__construct($message, $code, $previous);
	}
}
