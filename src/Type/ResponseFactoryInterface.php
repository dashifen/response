<?php

namespace Dashifen\Response\Factory;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseTypeInterface
 *
 * @package Dashifen\Response\Type
 */
interface ResponseFactoryInterface {
	/**
	 * @param string $html
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newHtmlResponse(string $html, int $statusCode = 200): ResponseInterface;
	
	/**
	 * @param string $json
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newJsonResponse(string $json, int $statusCode = 200): ResponseInterface;
	
	/**
	 * @param string $test
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newTextResponse(string $test, int $statusCode = 200): ResponseInterface;
	
	/**
	 * @param string $url
	 *
	 * @return ResponseInterface
	 */
	public function newRedirectResponse(string $url, int $statusCode = 302): ResponseInterface;
}
