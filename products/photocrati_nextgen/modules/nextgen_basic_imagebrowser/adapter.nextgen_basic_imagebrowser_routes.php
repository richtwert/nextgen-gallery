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
		$this->object->rewrite('nggallery{*}/image/{\d}', 'nggallery{1}/pid--{2}');
	}
}