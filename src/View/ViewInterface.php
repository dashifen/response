<?php

namespace Dashifen\Response\View;

/**
 * Interface ViewInterface
 *
 * @package Dashifen\Response\View
 */
interface ViewInterface {
	public const pattern = '/\\$(\w+)/';
	
	/**
	 * @param string $content
	 * @param array  $data
	 * @param string $pattern
	 *
	 * @return string
	 */
	public static function compileTemplate(string $content, array $data, string $pattern = ViewInterface::pattern): string;
	
	/**
	 * @param string $pattern
	 *
	 * @throws ViewException
	 * @return array
	 */
	public function getPrerequisites(string $pattern = ViewInterface::pattern): array;
	
	/**
	 * @param array $data
	 *
	 * @throws ViewException
	 * @return void;
	 */
	public function setData(array $data): void;
	
	/**
	 * @param string $index
	 * @param mixed  $datum
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setDatum(string $index, $datum): void;
	
	/**
	 * @param string $header
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setHeader(string $header): void;
	
	/**
	 * @param string $content
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setContent(string $content): void;
	
	/**
	 * @param string $footer
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setFooter(string $footer): void;
	
	/**
	 * @param array $data
	 *
	 * @throws ViewException
	 * @return string
	 */
	public function compile(array $data = []): string;
}
