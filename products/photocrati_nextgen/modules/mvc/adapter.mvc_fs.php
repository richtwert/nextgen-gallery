<?php

class A_MVC_Fs extends Mixin
{
	/**
	 * Gets the absolute path to a static resource
	 * @param type $path
	 * @param type $module
	 * @param type $relative
	 * @return type
	 */
	function get_static_abspath($path, $module=FALSE, $relative=FALSE)
	{
		$settings	  = $this->get_registry()->get_utility('I_Settings_Manager');

		// Build a list of paths of search for static resources
		$static_paths = array();
		foreach ($this->object->get_search_paths($path, $module) as $dir) {
			$static_paths[] = $this->object->join_paths(
				$dir, $settings->mvc_static_dirname
			);
			$static_paths[] = $dir;
		}

		return $this->object->get_abspath($path, $module, $relative, $static_paths);
	}

	/**
	 * Gets the relative path to a static resource
	 * @param string $path
	 * @param string $module
	 * @return string
	 */
	function get_static_relpath($path, $module=FALSE)
	{
		return $this->object->get_static_abspath($path, $module, TRUE);
	}
}