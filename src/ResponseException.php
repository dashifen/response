<?php

namespace Dashifen\Response;

use Dashifen\Exception\Exception;

class ResponseException extends Exception {
	public const AFTER_COMPILE_ALTERATION = 1;
	public const INCOMPLETE_COMPILATION = 2;
	public const UNKNOWN_RESPONSE_TYPE = 3;
	public const UNEXPECTED_RESPONSE = 4;
	public const RECOMPILATION = 5;
	public const INVALID_URL = 6;
	public const UNKNOWN_ERROR = 7;
}
