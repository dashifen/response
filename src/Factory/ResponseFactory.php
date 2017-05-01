<?php

namespace Dashifen\Response\Factory;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

/**
 * Class ResponseFactory
 *
 * @package Dashifen\Response\Factory
 */
class ResponseFactory implements ResponseFactoryInterface {
	/**
	 * @param string $html
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newHtmlResponse(string $html, int $statusCode = 200): ResponseInterface {
		return new HtmlResponse($html, $statusCode);
	}
	
	/**
	 * @param string $json
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newJsonResponse(string $json, int $statusCode = 200): ResponseInterface {
		return new JsonResponse($json, $statusCode);
	}
	
	/**
	 * @param string $test
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newTextResponse(string $test, int $statusCode = 200): ResponseInterface {
		return new TextResponse($test, $statusCode);
	}
	
	/**
	 * @param string $url
	 * @param int    $statusCode
	 *
	 * @return ResponseInterface
	 */
	public function newRedirectResponse(string $url, int $statusCode = 302): ResponseInterface {
		return new RedirectResponse($url, $statusCode);
	}
	
}
