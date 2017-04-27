<?php

namespace Dashifen\Response;

use Dashifen\Response\View\ViewInterface;

/**
 * Interface ResponseInterface
 *
 * @package Dashifen\Response
 */
interface ResponseInterface {
	/**
	 * @returns string
	 */
	public function getType(): string;
	
	/**
	 * @param string $type
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setType(string $type): void;
	
	/**
	 * @return int
	 */
	public function getStatusCode(): int;
	
	/**
	 * @param int $statusCode
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setStatusCode(int $statusCode): void;
	
	/**
	 * @param array $data
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setData(array $data): void;
	
	/**
	 * @param string $index
	 * @param mixed  $datum
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setDatum(string $index, $datum): void;
	
	/**
	 * @return ViewInterface
	 */
	public function getView(): ViewInterface;
	
	/**
	 * @param ViewInterface $view
	 *
	 * @throws ResponseException
	 * @return void
	 */
	public function setView(ViewInterface $view): void;
	
	/**
	 * @param string $content
	 *
	 * @return void
	 */
	public function setContent(string $content): void;
	
	/**
	 * sets the appropriate type and data for a redirection
	 *
	 * @param string $url;
	 *
	 * @return void
	 * @throws ResponseException
	 */
	public function redirect(string $url): void;
	
	/**
	 * @return bool
	 */
	public function isComplete(): bool;
	
	/**
	 * @return bool
	 */
	public function isCompiled(): bool;
	
	/**
	 * @throws ResponseException
	 * @return void
	 */
	public function compile(): void;
	
	/**
	 * @return void
	 */
	public function send(): void;
	
	/**
	 * displays a successful response
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function handleSuccess(array $data = []): void;
	
	/**
	 * displays an failed response but not one that produces an error.  e.g.,
	 * a domain read action that doesn't get anything or an create that fails.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function handleFailure(array $data = []): void;
	
	/**
	 * displays an erroneous response -- usually when catching an exception
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function handleError(array $data = []): void;
	
	/**
	 * displays a page-not-found (i.e. a HTTP 404 error)
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function handleNotFound(array $data = []): void;
}

