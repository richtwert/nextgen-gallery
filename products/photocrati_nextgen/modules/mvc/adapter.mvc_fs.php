<?php

class A_MVC_Fs extends Mixin
{
	/**
	 * Gets the absolute path to a static resource. If it doesn't exist, then NULL is returned
     *
	 * @param string $path
	 * @param string $module
	 * @param string $relative
	 * @return string|NULL
	 */
	function find_static_abspath($path, $module = FALSE, $relative = FALSE)
	{
                $settings = $this->get_registry()->get_utility('I_Settings_Manager');
            
		return $this->object->find_abspath(
                    $this->object->join_paths($settings->mvc_static_dirname, $path),
                    $module, $relative
                );
	}

	/**
	 * Gets the relative path to a static resource. If it doesn't exist, then NULL is returned
     *
	 * @param string $path
	 * @param string $module
	 * @return string|NULL
	 */
	function find_static_relpath($path, $module = FALSE)
	{
		return $this->object->find_static_abspath($path, $module, TRUE);
	}
}