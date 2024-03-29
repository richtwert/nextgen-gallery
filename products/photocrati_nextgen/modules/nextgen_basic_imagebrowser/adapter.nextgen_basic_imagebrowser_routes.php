<?php

class A_NextGen_Basic_ImageBrowser_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Routes for NextGen Basic ImageBrowser',
			get_class(),
			'_add_nextgen_basic_imagebrowser_routes'
		);
	}

	function _add_nextgen_basic_imagebrowser_routes()
	{
        $slug = $this->object->get_registry()->get_utility('I_Settings_Manager')->router_param_slug;
        $this->object->rewrite("{$slug}{*}/image/{\\w}", "{$slug}{1}/pid--{2}");
	}
}