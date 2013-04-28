<?php

class A_MVC_Router extends Mixin
{
	/**
	 * First tries to find the static file in the 'static' folder
	 * @param string $path
	 * @param string $module
	 * @return string
	 */
	function get_static_url($path, $module=FALSE)
	{
		$mod_path = $this->get_registry()->get_utility('I_Fs')->find_static_abspath($path, $module);
		return $this->call_parent('get_static_url', $mod_path ? $mod_path : $path);
	}
}