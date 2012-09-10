<?php

class C_Lazy_Resource_Loader extends C_Component
{
	static $_instances	= array();
	var $script_urls	= array();
	var $style_urls		= array();

	/**
	 * Gets an instance of this class
	 * @param mixed $context
	 * @return C_Lazy_Resource_Loader
	 */
	static function get_instance($context=FALSE)
	{
		if (!$context) $context = 'all';
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		parent::define($context);
		$this->implement('I_Lazy_Resource_Loader');
	}


	/**
	 * Initializes the loader by collecting the scripts and styles to lazy load
	 */
	function initialize()
	{
		parent::initialize();
		ob_start();
		wp_print_scripts();
		$this->script_urls = $this->_parse_resource_urls(ob_get_contents());
		ob_end_clean();
		ob_start();
		wp_print_styles();
		$this->style_urls = $this->_parse_resource_urls(ob_get_contents());
		ob_end_clean();
	}


	/**
	 * Parses HTML for urls of static resources
	 */
	function _parse_resource_urls($html)
	{
		$urls = array();
		if (preg_match_all("/(src|href)=['\"]([^'\"]+)/", $html, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				if (isset($match[2])) {
					$urls[] = $match[2];
				}
			}
		}
		return $urls;
	}

	/**
	 * Enqueue the scripts and styles using lazy loader
	 * @param type $return
	 * @return type
	 */
	function enqueue($return=FALSE)
	{
		$out = implode("\n", array(
			"\n<script type='text/javascript'>",
			"lazy_script_urls = ".json_encode($this->script_urls).";",
			"lazy_style_urls  = ".json_encode($this->style_urls).";",
			"</script>"
		));

		if (!$return) echo $out;

		return $out;
	}
}