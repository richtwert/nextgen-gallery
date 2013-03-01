<?php

class A_NextGen_Admin_Settings extends Mixin
{
	function initialize()
	{
		$router	  = $this->get_registry()->get_utility('I_Router');
		$uri	  = 'nextgen_admin#jquery-ui/jquery-ui-1.9.1.custom';
		$settings = array(
			'jquery_ui_theme'			=>	'jquery-ui-nextgen',
			'jquery_ui_theme_version'	=>	1.8,
			'jquery_ui_theme_url'		=>	$router->get_static_url($uri)
		);

		foreach ($settings as $key=>$val) $this->object->set_default($key, $val);
	}
}