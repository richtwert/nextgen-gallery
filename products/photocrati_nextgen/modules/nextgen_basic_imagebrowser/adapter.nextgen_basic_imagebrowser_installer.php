<?php

class A_NextGen_Basic_ImageBrowser_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_nextgen_basic_imagebrowser'
		);
	}

	function install_nextgen_basic_imagebrowser()
	{
		$this->object->install_display_type(
			NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER, array(
				'title'					=>	'NextGEN Basic ImageBrowser',
				'entity_types'			=>	array('image'),
				'preview_image_relpath'	=>	'nextgen_basic_imagebrowser#preview.jpg',
				'default_source'		=>	'galleries',
				'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 20
			)
		);
	}
}
