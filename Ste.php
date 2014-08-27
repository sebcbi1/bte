<?php
/**
 * @package    SugiPHP
 * @subpackage STE
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\STE;

/**
 * Sugi Template Engine
 *
 * <code>
 * $tpl = new Ste();
 * $tpl->load(path/to/template);
 *
 * $tpl->set('varname', 'value');
 * $tpl->set('varname', array('subvar1'=> 'value', 'subvar2' => 'onothervalue'));
 * $tpl->set(array('varname1' => 'value1', 'varname2' => 'value2'));
 * $tpl->loop('blockname', array( array('var1' => 'val1', 'var2' => 'val2'), array('var1' => 'otherval') ));
 * $tpl->hide('unwantendblock');
 * $tpl->unhide('someblockthatwashidden');
 * $tpl->registerFunction('__', '__'); // for translations
 *
 * echo $tpl->parse();
 * </code>
 */
class Ste
{
	/**
	 * Regular Expression for blocks
	 * <code>
	 * <!-- BEGIN blockname -->
	 * ...
	 * <!-- END blockname -->
	 * </code>
	 *
	 * It worked with only 's' regex modifier.
	 * with 'smU' regex modifiers it will parse all blocks with the same name
	 */
	protected $blockRegEx;

	/**
	 * Regular Expression for file inclusion
	 * <code>
	 * <!-- INLUDE filename.html -->
	 * </code>
	 */
	// protected $includeRegEx = '#<!--\s+INCLUDE\s+([_a-zA-Z0-9\-\.\/]+)\s+-->#sm';
	protected $includeRegEx;// = '#{include\s+([_a-zA-Z0-9\-\.\/]+)}#sm'; // include with {include filename.html}


	/**
	 * Regular Expression Pattern for variables
	 * <code>
	 * {varname}
	 * </code>
	 */
	protected $varRegEx;

	/**
	 * Regular Expression Pattern for array keys
	 * <code>
	 * {array.key.subkey}
	 * </code>
	 */
	protected $arrRegEx;

	/**
	 * Regular Expression Pattern for functions
	 * <code>
	 * {trans('Hello world')}
	 * </code>
	 */
	protected $funcRegEx;

	/**
	 * Regular Expression Pattern for removing html comments
	 * @var string
	 */
	protected $commentsRegEx = '#<!--(.|\s)*?-->#';

	/**
	 * Configuration options
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Loaded template
	 *
	 * @var string
	 */
	protected $tpl;

	/**
	 * Variables set with set() method
	 *
	 * @var array
	 */
	protected $vars = array();

	/**
	 * Variables for loops
	 *
	 * @var array
	 */
	protected $loops = array();

	/**
	 * Which blocks are set not to be rendered
	 *
	 * @var array
	 */
	protected $hide = array();

	/**
	 * Registered functions
	 *
	 * @var array
	 */
	protected $functions = array();

	/**
	 * Current include path based on last proceeded file
	 *
	 * @var string
	 */
	protected $includePath;

	/**
	 * Time spent parsing.
	 *
	 * @var integer
	 */
	protected $parseTime = 0;
	protected $timerTime;

	/**
	 * STE Constructor.
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		// default configurations
		$defaultConfig = array(
			"remove_comments"    => false,
			"default_path"       => false,
			// Template file extensions that are allowed
			"allowed_extensions" => array("html", "ste", "tpl", "txt"),
			"start_symbol"       => "{",
			"end_symbol"         => "}",
			"start_include"      => "<!-- INCLUDE ", // "{include"
			"end_include"        => " -->",          // "}"
			"start_begin_block"  => "<!-- BEGIN ",   // "{block"
			"start_end_block"    => "<!-- END ",     // "{endblock"
			"end_block"          => " -->",          // "}"
		);

		// set custom configurations
		foreach ($defaultConfig as $key => $value) {
			$this->config[$key] = isset($config[$key]) ? $config[$key] : $value;
		}
	}

	/**
	 * Sets raw template.
	 *
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->tpl = $template;
	}

	/**
	 * Returns raw template.
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->tpl;
	}

	/**
	 * Loads a template file.
	 *
	 * @param  string filename
	 * @return string
	 */
	public function load($templateFile)
	{
		$template = $this->loadFile($templateFile);
		$this->setTemplate($template);

		return $template;
	}

	/**
	 * This method combines set() and loop() methods
	 *
	 * @param  mixed $var
	 * @param  mixed $value
	 */
	public function assign($var, $value)
	{
		// check the value is associative array
		if (is_array($value) && array_values($value) === $value || $value === false) {
			// this means we want to loop
			return $this->loop($var, $value);
		}

		// set a variable
		return $this->set($var, $value);
	}

	/**
	 * Sets a parameter, or key=>value list
	 * examples:
	 * <code>
	 * set('title', 'My Title'); // sets the title key
	 * set('title'); // unsets the title key
	 * set(array('title' => 'My Title', 'description' => 'My Description')); // sets 2 keys: title and description
	 * set('home', array('link' => '/', 'title' => 'Home')); // sets a key 'home' which is an array and can be accessed with {home.link} and {home.title}
	 * </code>
	 *
	 * @param mixed $var
	 * @param mixed $value
	 */
	public function set($var, $value)
	{
		if (is_array($var)) {
			$this->vars = array_merge($this->vars, $var);
		} elseif (is_array($value) && isset($this->vars[$var]) && is_array($this->vars[$var])) {
			$this->vars[$var] = array_merge($this->vars[$var], $value);
		} else {
			$this->vars[$var] = $value;
		}
	}

	/**
	 * Returns previously set variable.
	 *
	 * @param  string $var
	 * @return mixed
	 */
	public function get($var)
	{
		return isset($this->vars[$var]) ? $this->vars[$var] : null;
	}

	/**
	 * Returns if variable is set or not.
	 *
	 * @param  string $var
	 * @return boolean
	 */
	public function has($var)
	{
		return isset($this->vars[$var]);
	}

	/**
	 * Removes variable.
	 *
	 * @param string $var
	 */
	public function delete($var)
	{
		unset($this->vars[$var]);
	}

	/**
	 * Loops a block (copies) several times replacing all nested variables with $values
	 *
	 * @param  string $blockname name of the block
	 * @param  array  $values  array of array of values
	 */
	public function loop($blockname, $values = array())
	{
		$this->loops[$blockname] = $values;
		//$this->vars[$blockname] = $values;
	}

	/**
	 * Hides (removes) a block.
	 *
	 * @param string $blockname
	 */
	public function hide($blockname)
	{
		$this->hide[$blockname] = true;
	}

	/**
	 * Unhides a block.
	 *
	 * @param string $blockname
	 */
	public function unhide($blockname)
	{
		unset($this->hide[$blockname]);
	}

	/**
	 * Sets a callback function.
	 * The callback function is invoked each time the template engine
	 * finds a call to it. In template it should be like {functionname(functionparamethers)}
	 *
	 * @param string function name
	 * @param callback
	 */
	public function registerFunction($name, $callback)
	{
		$this->functions[$name] = $callback;
	}

	/**
	 * Parses and returns prepared template
	 *
	 * @return string
	 */
	public function parse()
	{
		$this->parseTime = 0;
		$this->prepareRegEx();

		$tpl = $this->parseBlock($this->tpl);
		if ($this->config["remove_comments"]) {
			$tpl = $this->removeHtmlComments($tpl);
		}

		return $tpl;
	}

	/**
	 * Returns time (in seconds) spent parsing the template.
	 *
	 * @return float
	 */
	public function getParseTime()
	{
		return $this->parseTime;
	}

	protected function startTimer()
	{
		if ($this->timerTime) {
			$this->stopTimer();
		}

		$this->timerTime = microtime(true);
	}

	protected function stopTimer()
	{
		if ($this->timerTime) {
			$this->parseTime += microtime(true) - $this->timerTime;
			$this->timerTime = 0;
		}
	}

	protected function loadFile($templateFile)
	{
		// check file extension
		$ext = pathinfo($templateFile, PATHINFO_EXTENSION);
		if (!in_array($ext, $this->config["allowed_extensions"])) {
			throw new Exception("File $templateFile has extension that is not allowed template extension");
		}

		// try to load a file
		$template = $this->loadTemplateFile($templateFile);
		if (($template === false) && $this->config["default_path"] && (strpos($templateFile, DIRECTORY_SEPARATOR) !== 0)) {
			// adding default path to the template
			$templateFile = rtrim($this->config["default_path"], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $templateFile;
			// try to load a file from default include path
			$template = $this->loadTemplateFile($templateFile);
		}
		if ($template === false) {
			throw new Exception("Could not load template file $templateFile");
		}

		$this->include_path = realpath(dirname($templateFile) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		return $template;
	}

	/**
	 * Trying to get the contents of the template file.
	 * The file should exists and should be readable. If not false will be returned.
	 *
	 * @param  string $filename
	 * @param  string $default
	 * @return string
	 */
	protected function loadTemplateFile($filename)
	{
		return (is_file($filename) && is_readable($filename)) ? file_get_contents($filename) : false;
	}

	protected function prepareRegEx()
	{
		$startSymbol = preg_quote($this->config["start_symbol"], "#");
		$endSymbol = preg_quote($this->config["end_symbol"], "#");
		$this->varRegEx = "#".$startSymbol.'([_a-zA-Z][_a-zA-Z0-9]*)'.$endSymbol.'#sm';
		$this->arrRegEx = "#".$startSymbol.'([_a-zA-Z][_a-zA-Z0-9]*\.[_a-zA-Z][_a-zA-Z0-9\.]*)'.$endSymbol.'#sm';
		$this->funcRegEx = "#".$startSymbol.'([_a-zA-Z][_a-zA-Z0-9]*)\(([^\)]*)\)'.$endSymbol.'#sm';

		$startInclude = preg_quote($this->config["start_include"], "#");
		$endInclude = preg_quote($this->config["end_include"], "#");
		// #<!--\s+INCLUDE\s+([_a-zA-Z0-9\-\.\/]+)\s+-->#sm
		$this->includeRegEx = "#".$startInclude.'\s*([_a-zA-Z0-9\-\.\/]+)\s*'.$endInclude.'\s*#sm';

		// #<!--\s+BEGIN\s+([0-9A-Za-z._-]+)\s+-->(.*)<!--\s+END\s+\1\s+-->#smU
		$startBeginBlock = preg_quote($this->config["start_begin_block"], "#");
		$startEndBlock = preg_quote($this->config["start_end_block"], "#");
		$endBlock = preg_quote($this->config["end_block"], "#");
		$this->blockRegEx = "#".$startBeginBlock.'\s*([0-9A-Za-z._-]+)\s*'.$endBlock.'(.*)'.$startEndBlock.'\s*\1\s*'.$endBlock.'#smU';
	}

	protected function parseBlock($subject)
	{
		$this->startTimer();

		// blocks
		$subject = preg_replace_callback($this->blockRegEx, array(&$this, "replaceBlockCallback"), $subject);
		// replace variables
		$subject = preg_replace_callback($this->varRegEx, array(&$this, "replaceVarCallback"), $subject);
		// replace arrays
		$subject = preg_replace_callback($this->arrRegEx, array(&$this, "replaceArrCallback"), $subject);
		// invoke functions
		$subject = preg_replace_callback($this->funcRegEx, array(&$this, "replaceFuncCallback"), $subject);
		// check for dynamically included files
		$subject = preg_replace_callback($this->includeRegEx, array(&$this, "replaceIncludesCallback"), $subject);

		$this->stopTimer();

		return $subject;
	}

	protected function replaceFuncCallback($matches)
	{
		$callback = $matches[1];
		if (!isset($this->functions[$callback])) {
			return false;
		}
		// timer is stopped, because we don't want to count the time spent on user's functions
		$this->stopTimer();
		if ($args = json_decode("[" . $matches[2] ."]", true)) {
			$result = call_user_func_array($this->functions[$callback], $args);
		} else {
			$result = call_user_func($this->functions[$callback]);
		}
		$this->startTimer();

		return $result;
	}

	protected function replaceIncludesCallback($matches)
	{
		$oldIncludePath = $this->include_path;
		$part = $this->parseBlock($this->loadFile($this->include_path.$matches[1]));
		$this->include_path = $oldIncludePath;

		return $part;
	}

	protected function replaceVarCallback($matches)
	{
		if (isset($this->vars[$matches[1]])) {
			if (is_array($this->vars[$matches[1]])) {
				return $this->vars[$matches[1]][0];
			} else {
				return $this->vars[$matches[1]];
			}
		}

		return false;
	}

	protected function replaceArrCallback($matches)
	{
		$keys = explode('.', $matches[1]);
		$vars = $this->vars;
		foreach ($keys as $key => $val) {
			if (!isset($vars[$val])) {
				return false;
			}
			$vars = $vars[$val];
		}

		return $vars;
	}

	protected function replaceBlockCallback($matches)
	{
		static $inloop = false;

		// $matches[0] = block
		// $matches[1] = block name
		// $matches[2] = block content

		// check the block is hidden
		if (!empty($this->hide[$matches[1]])) {
			return false;
		}

		// if we have no registered loop
		if (!isset($this->loops[$matches[1]])) {
			// return false;
			if ($inloop) {
				$inloop = false;
				return false;
			}
			// parse inside
			return $this->parseBlock($matches[2]);
		}

		if (!is_array($this->loops[$matches[1]])) {
			if (!empty($this->loops[$matches[1]])) {
				// parse inside
				return $this->parseBlock($matches[2]);
			}

			return false;
		}

		// loop
		$return = '';
		$num = 0;
		foreach ($this->loops[$matches[1]] as $key => $match) {
			$inloop = true;
			$kk = array();
			if (is_array($match)) {
				foreach ($match as $k => $m) {
					if (is_array($m)) {
						$this->loops[$k] = $m;
					} elseif ($m !== false) {
						$this->loops[$k] = true;
					} elseif ($m === false) {
						$this->loops[$k] = false;
					}
					$kk[] = $k;
				}
				$match["_count"] = count($this->loops[$matches[1]]);
				$match["_num"] = ++$num;
				$match["_parity"] = $num % 2 ? "odd" : "even";
			}
			$this->vars[$matches[1]] = $match;
			$return .= $this->parseBlock($matches[2]);

			// cleanup
			foreach ($kk as $k) {
				unset($this->loops[$k]);
			}
			unset($this->vars[$matches[1]]);
			$inloop = false;
		}
		return $return;
	}

	/**
	 * Removes unwanted HTML comments
	 *
	 * @param  string $subject
	 * @return string
	 */
	protected function removeHtmlComments($subject)
	{
		return preg_replace($this->commentsRegEx, "", $subject);
	}

	/**
	 * @deprecated use has($var);
	 */
	public function hasVar($var)
	{
		return $this->has($var);
	}

	/**
	 * Sets raw template. If used with no parameter only returns raw template.
	 *
	 * @param      string $template
	 * @return     string
	 * @deprecated Use setTemplate() / getTemplate instead
	 */
	public function template($template = null)
	{
		if (!is_null($template)) {
			$this->setTemplate($template);
		}

		return $this->tpl;
	}
}
