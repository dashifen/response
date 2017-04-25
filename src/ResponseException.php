<?php

namespace Dashifen\Response;

use Throwable;

class ResponseException extends \Exception {
	public const AFTER_COMPILE_ALTERATION = 1;
	public const INCOMPLETE_COMPILATION = 2;
	public const RECOMPILATION = 3;
	public const UNKNOWN_ERROR = 4;
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$reflection = new \ReflectionClass(__CLASS__);
		if (!in_array($code, $reflection->getConstants())) {
			$code = self::UNKNOWN_ERROR;
		}
		
		parent::__construct($message, $code, $previous);
	}
}
