<?php

class A_Attach_To_Post_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_attach_to_post_module'
		);
	}

	function install_attach_to_post_module($product)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->add_capability(NEXTGEN_GALLERY_ATTACH_TO_POST_SLUG);
	}
}