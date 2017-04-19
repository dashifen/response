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
}
