<?php

namespace Dashifen\Response;

use Dashifen\Response\Factory\ResponseFactoryInterface;
use Dashifen\Response\View\ViewInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * Class Response
 *
 * @package Dashifen\Response
 */
abstract class AbstractResponse implements ResponseInterface {
	/**
	 * @var string $type
	 */
	protected $type;
	
	/**
	 * @var int $statusCode
	 */
	protected $statusCode;
	
	/**
	 * @var array $data
	 */
	protected $data = [];
	
	/**
	 * @var ViewInterface $view
	 */
	protected $view;
	
	/**
	 * @var HttpResponseInterface $response
	 */
	protected $response;
	
	/**
	 * @var EmitterInterface $emitter
	 */
	protected $emitter;
	
	/**
	 * @var ResponseFactoryInterface $responseFactory
	 */
	protected $responseFactory;
	
	/**
	 * @var string $root_path
	 */
	protected $root_path = "";
	
	/**
	 * @var bool $complete
	 */
	protected $compiled = false;
	
	/**
	 * @var string $completenessError
	 */
	protected $completenessError = "";
	
	/**
	 * Map of standard HTTP status code/reason phrases
	 * Copied from Zend\Diactoros\Response\Response 2017-04-19
	 *
	 * @var array
	 */
	private $phrases = [
		// INFORMATIONAL CODES
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		// SUCCESS CODES
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		// REDIRECTION CODES
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		// CLIENT ERROR
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		444 => 'Connection Closed Without Response',
		451 => 'Unavailable For Legal Reasons',
		// SERVER ERROR
		499 => 'Client Closed Request',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
		599 => 'Network Connect Timeout Error',
	];
	
	/**
	 * Response constructor.
	 *
	 * @param ViewInterface            $view
	 * @param EmitterInterface         $emitter
	 * @param ResponseFactoryInterface $responseFactory
	 * @param string				   $root_path
	 */
	public function __construct(
		ViewInterface $view,
		EmitterInterface $emitter,
		ResponseFactoryInterface $responseFactory,
		string $root_path = ""
	) {
		$this->view = $view;
		$this->emitter = $emitter;
		$this->responseFactory = $responseFactory;
		
		// before we set our root path, if the final character of it is our
		// directory separator, we want to remove it.  this is because our
		// setContent method below will want to add it back in and we don't
		// want there to be two in a row.
		
		if (!empty($root_path) && substr($root_path, -1, 1) === DIRECTORY_SEPARATOR) {
			$root_path = substr($root_path, 0, strlen($root_path)-1);
		}
		
		$this->root_path = $root_path;
		
		// finally, until we're told otherwise, we're going to assume that
		// we're sending an html response and that it's successful.  if that
		// has to change, we can always do so elsewhere.
		
		$this->setStatusCode(200);
		$this->setType("html");
	}
	
	/**
	 * @returns string
	 */
	public function getType(): string {
		return $this->type;
	}
	
	/**
	 * @param string $type
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setType(string $type): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to alter response after compilation.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		$type = strtolower($type);
		
		if (!in_array($type, ["html", "json", "text", "redirect"])) {
			throw new ResponseException("Unexpected response type: $type.", ResponseException::UNKNOWN_RESPONSE_TYPE);
		}
		
		$this->type = $type;
	}
	
	/**
	 * @return int
	 */
	public function getStatusCode(): int {
		return $this->statusCode;
	}
	
	/**
	 * @param int $statusCode
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setStatusCode(int $statusCode): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to alter response after compilation.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		if (!in_array($statusCode, array_keys($this->phrases))) {
			throw new ResponseException("Unknown status code: $statusCode.");
		}
		
		$this->statusCode = $statusCode;
	}
	
	/**
	 * @param array $data
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setData(array $data): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to alter response after compilation.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		// it might appear, at first, that we could simply set
		// $this->data to $data and call it a day.  but, that would
		// overwrite anything that had also been set by setDatum()
		// below.  therefore, we'll perform a more careful assignment
		// here.
		
		foreach ($data as $index => $datum) {
			$this->setDatum($index, $datum);
		}
		
	}
	
	/**
	 * @param string $index
	 * @param mixed  $datum
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setDatum(string $index, $datum): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to alter response after compilation.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		$this->data[$index] = $datum;
	}
	
	/**
	 * @return ViewInterface
	 */
	public function getView(): ViewInterface {
		return $this->view;
	}
	
	/**
	 * @param ViewInterface $view
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setView(ViewInterface $view): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to alter response after compilation.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		$this->view = $view;
	}
	
	/**
	 * passes content to our view
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function setContent(string $content): void {
		
		// there's two options here:  that the concatenation of $this->root_path
		// and $content is a file and we pass that to our view or that it isn't
		// and we pass the $content argument to the view directly assuming that
		// it's a string of content.
		
		$temp = $this->root_path . DIRECTORY_SEPARATOR . $content;
		$this->view->setContent(is_file($temp) ? $temp : $content);
	}
	
	/**
	 * sets the appropriate type and data for a redirection
	 *
	 * @param string $url
	 *
	 * @throws ResponseException
	 */
	public function redirect(string $url): void {
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new ResponseException("Invalid URL: $url", ResponseException::INVALID_URL);
		}
		
		$this->setType("redirect");
		$this->data["url"] = $url;
	}
	
	/**
	 * @return bool
	 */
	public function isCompiled(): bool {
		return $this->compiled;
	}
	
	/**
	 * @throws ResponseException
	 * @return void
	 */
	public function send(): void {
		if (!$this->isComplete()) {
			throw new ResponseException("Attempt to send incomplete response: $this->completenessError.", ResponseException::INCOMPLETE_COMPILATION);
		}
		
		// in a perfect world, the programmer would always compile
		// before sending.  in practice, that can be a bit annoying.
		// so, if our response has not yet been compiled, then we'll
		// do so now.
		
		if (!$this->compiled) {
			$this->compile();
		}
		
		// now, with a compiled response, we can send (i.e. emit) it.
		
		$this->emitter->emit($this->response);
	}
	
	/**
	 * @return bool
	 */
	public function isComplete(): bool {
		$this->completenessError = "";
		
		// before we do anything, we need to be sure that we
		// have the following information: type, data, and view.
		// without those, it's impossible for us to be complete.
		
		if (is_null($this->data) || !is_array($this->data) || sizeof($this->data)===0) {
			$this->completenessError = "invalid data";
			return false;
		}
		
		if (is_null($this->type)) {
			$this->completenessError = "invalid type";
			return false;
		}
		
		if (is_null($this->view)) {
			$this->completenessError = "invalid view";
			return false;
		}
		
		
		if ($this->type !== "redirect") {
			
			// for our responses that aren't redirections, then we'll want
			// to be sure that we have all the data that our view expects.
			
			$keys = array_keys($this->data);
			$prerequisites = $this->view->getPrerequisites();
			$difference = array_diff($prerequisites, $keys);
			
			// array_diff() returns an array of items in $prerequisites
			// that are not found in $keys.  if $keys has extra information,
			// that's fine, but if it lacks anything in our $prerequisites
			// then we're not complete.  so, if the resulting $difference is
			// zero, then we can return true.
			
			if (sizeof($difference) !== 0) {
				$difference = join(", ", $difference);
				$this->completenessError = "missing data ($difference)";
				return false;
			}
			
			// if we didn't return false in the if-block above, then our
			// response is complete.  we'll return true here to avoid testing
			// the needs for a redirect response below.
			
			return true;
		}
		
		// if we're still executing this method, then we must be redirecting.
		// in this case, the only data we need is a url.  if we have it, and if
		// it appears to be valid, we'll be good to go.
		
		if (!isset($this->data["url"])) {
			$this->completenessError = "missing url";
			return false;
		}
		
		if (!filter_var($this->data["url"], FILTER_VALIDATE_URL)) {
			$this->completenessError = "invalid url";
			return false;
		}
		
		return true;
	}
	
	/**
	 * @throws ResponseException
	 * @return void
	 */
	public function compile(): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to recompile response.", ResponseException::RECOMPILATION);
		}
		
		if (!$this->isComplete()) {
			throw new ResponseException("Attempt to compile incomplete response: $this->completenessError.", ResponseException::AFTER_COMPILE_ALTERATION);
		}
		
		$content = $this->type !== "redirect"
			? $this->view->compile($this->data)
			: $this->data["url"];
		
		$this->response = $this->newResponse($content, $this->statusCode);
		$this->compiled = true;
	}
	
	/**
	 * @param string $content
	 * @param int    $statusCode
	 *
	 * @return HttpResponseInterface;
	 */
	protected function newResponse(string $content, int $statusCode = null): HttpResponseInterface {
		
		if (is_null($statusCode)) {
			$statusCode = $this->type === "redirect" ? 302 : 200;
		}
		
		switch ($this->type) {
			case "json":
				return $this->responseFactory->newJsonResponse($content, $statusCode);
			
			case "text":
				return $this->responseFactory->newTextResponse($content, $statusCode);
			
			case "redirect":
				return $this->responseFactory->newRedirectResponse($content, $statusCode);
			
			default:
				return $this->responseFactory->newHtmlResponse($content, $statusCode);
		}
	}
	
	/**
	 * displays a successful response
	 *
	 * @param array  $data
	 * @param string $action
	 *
	 * @return void
	 */
	abstract public function handleSuccess(array $data = [], string $action = "read"): void;
	
	/**
	 * displays an failed response but not one that produces an error.  e.g.,
	 * a domain read action that doesn't get anything or an create that fails.
	 *
	 * @param array  $data
	 * @param string $action
	 *
	 * @return void
	 */
	abstract public function handleFailure(array $data = [], string $action = "read"): void;
	
	/**
	 * displays an erroneous response -- usually when catching an exception
	 *
	 * @param array  $data
	 * @param string $action
	 *
	 * @return void
	 */
	abstract public function handleError(array $data = [], string $action = "read"): void;
	
	/**
	 * displays a page-not-found (i.e. a HTTP 404 error)
	 *
	 * @param array  $data
	 * @param string $action
	 *
	 * @return void
	 */
	abstract public function handleNotFound(array $data = [], string $action = "read"): void;
	
	
}
