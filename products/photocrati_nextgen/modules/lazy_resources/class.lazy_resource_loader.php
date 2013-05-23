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
		$this->add_mixin('Mixin_Lazy_Resource_Loader');
		$this->implement('I_Lazy_Resource_Loader');
	}


	/**
	 * Parses HTML for urls of static resources
	 */
	function _parse_resource_urls($html)
	{
		$urls = array();
		if (preg_match_all("/(href)=['\"]([^'\"]+)/", $html, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				if (isset($match[2])) {
					$urls[] = $match[2];
				}
			}
		}
		return $urls;
	}
}

class Mixin_Lazy_Resource_Loader extends Mixin
{
	/**
	 * Enqueue the scripts and styles using lazy loader
	 * @param type $return
	 * @return type
	 */
	function enqueue($return=FALSE)
	{
//        wp_print_scripts();
        ob_start();
        wp_print_styles();
        $this->style_urls = array_merge($this->style_urls, $this->_parse_resource_urls(ob_get_contents()));
        ob_end_clean();

		$out = array();

		if ($this->script_urls OR $this->style_urls) {
			$out[] = "\n<script type='text/javascript'>";
			foreach ($this->script_urls as $url) $out[] = "Lazy_Resources.script_urls.push(\"{$url}\");";
			foreach ($this->style_urls as $url) $out[] = "Lazy_Resources.style_urls.push(\"{$url}\");";
			$out[] = "Lazy_Resources.enqueue();";
			$out[] = "</script>";
			$out = implode("\n", $out);

			if (!$return) echo $out;
		}

		return $out;
	}
}