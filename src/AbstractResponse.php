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
	 * @var bool $complete
	 */
	protected $compiled = false;
	
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
	 */
	public function __construct(
		ViewInterface $view,
		EmitterInterface $emitter,
		ResponseFactoryInterface $responseFactory
	) {
		$this->view = $view;
		$this->emitter = $emitter;
		$this->responseFactory = $responseFactory;
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
			throw new ResponseException("Attempt to alter response after compilation.");
		}
		
		$type = strtolower($type);
		
		if (!in_array($type, ["html", "json", "text", "redirect"])) {
			throw new ResponseException("Unexpected response type: $type.");
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
			throw new ResponseException("Attempt to alter response after compilation.");
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
			throw new ResponseException("Attempt to alter response after compilation.");
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
			throw new ResponseException("Attempt to alter response after compilation.");
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
			throw new ResponseException("Attempt to alter response after compilation.");
		}
		
		$this->view = $view;
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
			throw new ResponseException("Attempt to send incomplete response.");
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
		
		// before we do anything, we need to be sure that we
		// have the following information: type, data, and view.
		// without those, it's impossible for us to be complete.
		
		if (sizeof($this->data) || is_null($this->type) || is_null($this->view)) {
			return false;
		}
		
		// now, if we have those, the other thing we want to be sure
		// of is that our data includes the information that our view
		// needs in order to compile our response.
		
		$keys = array_keys($this->data);
		$prerequisites = $this->view->getPrerequisites();
		$difference = array_diff($prerequisites, $keys);
		
		// array_diff() returns an array of items in $prerequisites
		// that are not found in $keys.  if $keys has extra information,
		// that's fine, but if it lacks anything in our $prerequisites
		// then we're not complete.  so, if the resulting $difference is
		// zero, then we can return true.
		
		return sizeof($difference) === 0;
	}
	
	/**
	 * @throws ResponseException
	 * @return void
	 */
	public function compile(): void {
		if ($this->compiled) {
			throw new ResponseException("Attempt to recompile response.");
		}
		
		if (!$this->isComplete()) {
			throw new ResponseException("Attempt to compile incomplete response.");
		}
		
		$content = $this->view->compile($this->data);
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
	 * @param array $data
	 *
	 * @return void
	 */
	abstract public function handleSuccess(array $data = []): void;
	
	/**
	 * displays an failed response but not one that produces an error.  e.g.,
	 * a domain read action that doesn't get anything or an create that fails.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	abstract public function handleFailure(array $data = []): void;
	
	/**
	 * displays an erroneous response -- usually when catching an exception
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	abstract public function handleError(array $data = []): void;
	
	/**
	 * displays a page-not-found (i.e. a HTTP 404 error)
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	abstract public function handleNotFound(array $data = []): void;
	
	
}
