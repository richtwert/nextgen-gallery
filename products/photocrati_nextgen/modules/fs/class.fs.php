<?php

class C_Fs extends C_Component
{
	static	$_instances = array();
	var		$_document_root;

	/**
	 * Gets an instance of the FS utility
	 * @param mixed $context
	 * @return C_Fs
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	/**
	 * Defines the instance of the FS utility
	 * @param mixed $context	the context in this case is the product
	 */
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Fs_Instance_Methods');
		$this->implement('I_Fs');
	}

	function initialize()
	{
		$this->_document_root = $this->set_document_root($_SERVER['DOCUMENT_ROOT']);
	}
}

class Mixin_Fs_Instance_Methods extends Mixin
{
	/**
	 * Gets the absolute path to a file/directory for a specific Pope product
	 * @param string $path
	 * @param string $module
	 */
	function get_abspath($path, $module=FALSE, $relpath=FALSE)
	{
		// Get a starting point
		$module_path = $this->get_registry()->get_product_module_path($this->context);
		if (!$module_path) $module_path = $this->object->get_document_root();
		$base_path = $module_path;

		// The path might be relative to a specific module
		if ($module) {

			// Perhaps the directory of the module was specified?
			$module_dir = $this->join_paths($module_path, $module);
			if (file_exists($module_dir)) $base_path = $module_dir;

			// Nope - perhaps the module id was specified instead?
			else {
				$module_dir = $this->join_paths(
					$module_path,
					$this->get_registry()->get_module_dir($module)
				);
				if (file_exists($module_dir)) $base_path = $module_dir;
			}
		}
		// Find the file/directory
		$retval = $this->_rglob($base_path, $path);
		if ($retval && $relpath) $retval = str_replace($base_path, '', $retval);
		return $retval;
	}

	/**
	 * Searches for a file recursively
	 * @param string $base_path
	 * @param string $file
	 * @param int $flags
	 * @return string
	 */
	function _rglob($base_path, $file, $flags=0)
	{
		$retval = NULL;
		$results = file_exists($this->join_paths($base_path, $file));

		// Must be located in a sub-directory
		if (!$results) {
			$results = glob($this->join_paths($base_path, '/*'), GLOB_ONLYDIR);
			foreach ($results as $dir) {
				if (strpos($dir, 'mvc') !== FALSE) {
					$this->debug = TRUE;
				}
				$retval = $this->_rglob($dir, $file, $flags);
				if ($retval) break;
			}
		}
		else $retval = $this->join_paths($base_path, $file);

		return $retval;
	}

	/**
	 * Gets the relative path to a file/directory for a specific Pope product
	 * @param type $path
	 * @param type $module
	 */
	function get_relpath($path, $module=FALSE)
	{
		return $this->object->get_abspath($path, $module, TRUE);
	}


	/**
	 * Joins multiple path segments together
	 * @return string
	 */
	function join_paths()
	{
		$segments = array();
		$retval = array();
		$params = func_get_args();
		$this->_flatten_array($params, $segments);

		foreach ($segments as $segment) {
			if (strpos($segment, '/') === 0)
                $segment = substr($segment, 1);
			if (substr($segment, -1) === '/')
                $segment = substr($segment, 0, -1);
			if ($segment) $retval[] = $segment;
		}
		$retval = implode('/', $retval);
		if (strpos($retval, '/') != 0 && strpos($retval, 'http') != 0)
            $retval = '/'.$retval;
		return $retval;
	}

	function _flatten_array($obj, &$arr)
	{
		if (is_array($obj)) {
			foreach ($obj as $inner_obj) $this->_flatten_array($inner_obj, $arr);
		}
		elseif ($obj) $arr[] = $obj;
	}

	/**
	 * Gets the document root for this application
	 * @return string
	 */
	function get_document_root()
	{
		return $this->_document_root;
	}

	/**
	 * Sets the document root for this application
	 * @param type $value
	 * @return type
	 */
	function set_document_root($value)
	{
		return $this->_document_root = untrailingslashit($value);
	}
}