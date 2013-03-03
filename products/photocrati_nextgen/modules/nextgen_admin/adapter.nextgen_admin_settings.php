<?php

class A_NextGen_Admin_Settings extends Mixin
{
	function get($key, $default=NULL)
	{
		$retval = NULL;

		switch ($key) {
			case 'jquery_ui_theme':
				$retval =  'jquery-ui-nextgen';
				break;

			case 'jquery_ui_theme_version':
				$retval =	1.8;
				break;

			case 'jquery_ui_theme_url':
				$router = $this->get_registry()->get_utility('I_Router');
				$uri    = 'nextgen_admin#jquery-ui/jquery-ui-1.9.1.custom.css';
				$retval	= $router->get_static_url($uri);
				break;

			default:
				$retval	= $this->call_parent('get', $key, $default);
				break;
		}

		return $retval;
	}
}