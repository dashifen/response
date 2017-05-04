<?php

namespace Dashifen\Response\View;

class View implements ViewInterface {
	/**
	 * @var array $data
	 */
	protected $data = [];
	
	/**
	 * @var string $header
	 */
	protected $header = "";
	
	/**
	 * @var string $content
	 */
	protected $content = "";
	
	/**
	 * @var string $footer
	 */
	protected $footer = "";
	
	/**
	 * @var string $compiled
	 */
	protected $compilation = "";
	
	/**
	 * @var bool compiled
	 */
	protected $compiled = false;
	
	/**
	 * View constructor.
	 *
	 * @param string $header
	 * @param string $footer
	 */
	public function __construct(string $header, string $footer) {
		$this->setHeader($header);
		$this->setFooter($footer);
	}
	
	/**
	 * @param string $content
	 * @param array  $data
	 * @param string $pattern
	 *
	 * @return string
	 */
	public static function compileTemplate(string $content, array $data, string $pattern = ViewInterface::pattern): string {
		
		// this is a very, very simple template engine.  we made it
		// static so that it could be used elsewhere in an application
		// using this interface if necessary.  but, for every matched
		// string in $content using $pattern, we replace that match
		// with a corresponding value within $data.  if an app needs a
		// better/stronger/faster template, then they can override
		// this one
		
		return preg_replace_callback($pattern, function($matches) use ($data) {
			
			// in our $matches array, the zeroth index is the text
			// within $content that was matched by our $pattern.  this
			// match includes the dollar sign that indicates the
			// beginning of a replacement, but we don't expect that
			// our $data array will use those.  so, we first test to
			// see if $data has an index without the dollar sign, then
			// with, and if we find neither, we return an error.
			
			return $data[$matches[1]] ?? "#ERROR EXPECTING $matches[1]#";
			
		}, $content);
	}
	
	/**
	 * @param string $pattern
	 *
	 * @throws ViewException
	 * @return array
	 */
	public function getPrerequisites(string $pattern = ViewInterface::pattern): array {
		
		//x by default, the $pattern for this method matches the one
		// for the applyTemplate() method above, and both match the
		// public constant from the ViewInterface.  you can change the
		// pattern, but they'd better be the same, or you'll get funky
		// results.
		
		preg_match_all($pattern, $this->content, $matches);
		
		// the $matches array should have two indices.  the first is the
		// actual matched text which, by default, includes the dollar sign
		// that identifies template variables.  but, the second index is
		// the parenthetical match, which is the word following the dollar
		// sign.  so, all we want to do is send back that array but without
		// any duplicates as follows.
		
		return array_unique($matches[1]);
	}
	
	/**
	 * @param array $data
	 *
	 * @throws ViewException
	 * @return void;
	 */
	public function setData(array $data): void {
		if ($this->compiled) {
			throw new ViewException("Attempt to alter view after compilation.", ViewException::AFTER_COMPILE_ALTERATION);
		}
		// to avoid overwriting any data that has already been set
		// using setDatum(), we don't want to do a simple assignment.
		// instead, we'll more carefully copy information from $data
		// to the data property.
		
		foreach ($data as $index => $datum) {
			$this->setDatum($index, $datum);
		}
	}
	
	/**
	 * @param string $index
	 * @param mixed  $datum
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setDatum(string $index, $datum): void {
		if ($this->compiled) {
			throw new ViewException("Attempt to alter view after compilation.", ViewException::AFTER_COMPILE_ALTERATION);
		}
		
		$this->data[$index] = $datum;
	}
	
	/**
	 * @param string $index
	 *
	 * @return bool
	 */
	public function has(string $index): bool {
		return isset($this->data[$index]);
	}
	
	/**
	 * @param string $header
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setHeader(string $header): void {
		if ($this->compiled) {
			throw new ViewException("Attempt to alter view after compilation.", ViewException::AFTER_COMPILE_ALTERATION);
		}
		
		// $header can be either a file or a string containing the
		// header for the actual display of our view.
		
		$this->header = is_file($header)
			? file_get_contents($header)
			: $header;
	}
	
	/**
	 * @param string $content
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setContent(string $content): void {
		if ($this->compiled) {
			throw new ViewException("Attempt to alter view after compilation.", ViewException::AFTER_COMPILE_ALTERATION);
		}
		
		// like the header above, $content might be a file or the
		// actual content
		
		$this->content = is_file($content)
			? file_get_contents($content)
			: $content;
	}
	
	/**
	 * @param string $footer
	 *
	 * @throws ViewException
	 * @return void
	 */
	public function setFooter(string $footer): void {
		if ($this->compiled) {
			throw new ViewException("Attempt to alter view after compilation.", ViewException::AFTER_COMPILE_ALTERATION);
		}
		
		// like the header above, $footer might be a file or the
		// actual footer
		
		$this->footer = is_file($footer)
			? file_get_contents($footer)
			: $footer;
	}
	
	/**
	 * @param array $data
	 *
	 * @throws ViewException
	 * @return string
	 */
	public function compile(array $data = []): string {
		
		// this is our most basic version of a compilation.  it
		// simply assumes that whatever information we have in our
		// data (either the argument or the combination of it and
		// the data property) should just get crammed into our
		// content.  we expect that more complicated views will
		// require more work and so they can overwrite this method.
		
		if ($this->compiled) {
			throw new ViewException("Attempt to recompile view.", ViewException::RECOMPILATION);
		}
		
		// for the convenience of our programmers, we'll let more
		// data be added here -- or maybe it'll be the only way
		// someone adds data.
		
		$this->setData($data);
		
		// now, we'll want to merge our header and footer and put
		// content in the middle.  if we've not received specific
		// content, we just assume it'll be found in our data.
		// then, we can apply our template compiler
		
		if (empty($this->content)) {
			$this->setContent('$content');
		}
		
		$this->compilation = View::compileTemplate(
			($this->header . $this->content . $this->footer),
			$this->data);
		
		// so that we lock our view after compilation, we'll set the
		// compiled flag and then return the content to the calling
		// scope.
		
		$this->compiled = true;
		return $this->compilation;
	}
}
