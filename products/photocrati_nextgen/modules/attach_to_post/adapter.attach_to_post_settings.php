<?php

class A_Attach_to_Post_Settings extends Mixin
{
	function initialize()
	{
        // TODO: Investigate this code. The router uses the I_Settings_Manager utility, but it looks like the..
        // I_Settings_Manager utility requires the router. Ugh.
		$router = $this->get_registry()->get_utility('I_Router');
		$settings = array(
			'attach_to_post_url'				=> $router->get_url('/nextgen-attach_to_post', FALSE),
			'gallery_preview_url'				=> $router->get_url('/nextgen-attach_to_post/preview', FALSE),
			'attach_to_post_display_tab_js_url'	=> $router->get_url('/nextgen-attach_to_post/display_tab_js', FALSE)
		);
		foreach ($settings as $key=>$val) $this->object->set_default($key, $val);
	}
}