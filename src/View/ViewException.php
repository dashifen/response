<?php

namespace Dashifen\Response\View;

use Dashifen\Exception\Exception;

class ViewException extends Exception {
	public const AFTER_COMPILE_ALTERATION = 1;
	public const RECOMPILATION = 2;
	public const UNKNOWN_ERROR = 3;
}
