<?php

class A_NextGen_Basic_TagCloud_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds Routes for NextGen Basic TagCloud',
			get_class(),
			'_add_nextgen_basic_tagcloud_routes'
		);
	}

	function _add_nextgen_basic_tagcloud_routes()
	{
        $this->object->rewrite('nggallery{*}/tags/{\w}{*}', 'nggallery{1}/gallerytag--{2}{3}');
	}
}