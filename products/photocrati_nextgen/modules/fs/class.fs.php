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
		parent::initialize();
		$this->_document_root = $this->set_document_root($_SERVER['DOCUMENT_ROOT']);
	}
}

class Mixin_Fs_Instance_Methods extends Mixin
{
    
        function add_trailing_slash($path)
        {
            if (substr($path, -1) != '/') $path .= '/';
            return $path;
        }
    
    
        /**
         * Returns a calculated path to a file
         * @param string $path
         * @param string $module
         * @param boolean $relpath
         * @returns string
         */
        function get_abspath($path, $module=FALSE, $relpath=FALSE)
        {
            // Wel'l assume that we're to calculate the path relative to
            // the site document root
            $retval = $this->object->join_paths(
                $this->object->get_document_root(),
                $path
            );
            
            // If a module is provided, then we should calculate the path
            // relative to the module directory
            if ($module) {
                if (($module_dir = $this->get_registry()->get_module_dir($module))) {
                    $retval = $this->object->join_paths($module_dir, $path);
                }
                else {
                    $retval = $this->object->join_path(
                        $this->object->get_document_root(), $module, $path
                    );
                }
            }
            
            // Return the calculated path relative to the document root
            if ($relpath) $retval = $this->object->remove_path_segment(
                $retval, $this->object->get_document_root()
            );
            
            return $retval;
        }
        
        
        /**
         * Returns a calculated relpath to a particular file
         * @param string $path
         * @param string $module
         * @return string
         */
        function get_relpath($path, $module=FALSE)
        {
            return $this->object->get_abspath($path, $module, TRUE);
        }
        
        /**
         * Removes a path segment from a url or filesystem path
         * @param string $path
         * @param string $segment
         * @return string
         */
        function remove_path_segment($path, $segment)
        {
            if (substr($segment, -1) == '/') $segment = substr($segment, 0, -1);
            $parts = explode($segment, $path);
            return $this->object->join_paths($parts);
        }
    
    
	/**
	 * Gets the absolute path to a file/directory for a specific Pope product,
         * If the path doesn't exist, then NULL is returned
	 * @param string $path
	 * @param string $module
         * @returns string|NULL
	 */
	function find_abspath($path, $module=FALSE, $relpath=FALSE, $search_paths=array())
	{
		$retval = NULL;

		if (file_exists($path))
                    $retval = $path;

		else {
			// Ensure that we weren't passed a module id in the path
                        if (!$module) list($path, $module) = $this->object->parse_formatted_path($path);
                       

			// Ensure that we know where to search for the file
			if (!$search_paths) {
                            $search_paths = $this->object->get_search_paths($path, $module);
			}

			// Now that know where to search, let's find the file
			foreach ($search_paths as $dir) {
                            if (($retval = $this->object->_rglob($dir, $path))) {

                                if ($relpath) $retval = $this->object->remove_path_segment(
                                    $retval, $this->object->get_document_root()
                                );
                                break;
                            }
			}
		}

		return $retval;
	}

	/**
	 * Returns a list of directories to search for a particular filename
	 * @param string $path
	 * @param string $module
	 * @return array
	 */
	function get_search_paths($path, $module=FALSE)
	{
		$append_module = FALSE;

		// Ensure that we weren't passed a module id in the path
		if (!$module) list($path, $module) = $this->object->parse_formatted_path($path);

		// Directories to search
		$directories = array();

		// If a name of a module has been provided, then we need to search within
		// that directory first
		if ($module) {

			// Were we given a module id?
			if (($module_dir = $this->get_registry()->get_module_dir($module))) {
				$directories[] = $module_dir;
			}
			else {
				$append_module = TRUE;
			}
		}

		// Add product's module directories
		foreach ($this->get_registry()->get_product_list() as $product_id) {
			$product_dir = $this->get_registry()->get_product_module_path($product_id);
			if ($append_module) $directories[] = $this->object->join_paths(
				$product_dir, $module
			);
			$directories[] = $product_dir;
		}

		// If all else fails, we search from the document root
		$directories[] = $this->object->get_document_root();

		return $directories;
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
		$results = file_exists($this->object->join_paths($base_path, $file));

		// Must be located in a sub-directory
		if (!$results) {
			$results = (array) glob($this->object->join_paths($base_path, '/*'), GLOB_ONLYDIR|GLOB_NOSORT);
			foreach ($results as $dir) {
				$retval = $this->object->_rglob($dir, $file, $flags);
				if ($retval) break;
			}
		}
		else $retval = $this->object->join_paths($base_path, $file);

		return $retval;
	}

	/**
	 * Gets the relative path to a file/directory for a specific Pope product.
         * If the path doesn't exist, then NULL is returned
	 * @param type $path
	 * @param type $module
         * @returns string|NULL
	 */
	function find_relpath($path, $module=FALSE)
	{
		return $this->object->find_abspath($path, $module, TRUE);
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
                    if ($segment)
                        $retval[] = $segment;
		}

		$retval = implode('/', $retval);
                
                if (strpos($retval, '/') !== 0 && !preg_match("#^http(s)?://#", $retval))
                    $retval = '/' . $retval;

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
	 * Parses the path for a module and filename
	 * @param string $str
	 */
	function parse_formatted_path($str)
	{
		$module = FALSE;
		$path	= $str;
		$parts	= explode('#', $path);
		if (count($parts) > 1) {
			$module = array_shift($parts);
			$path   = array_shift($parts);
		}
		return array($path, $module);
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