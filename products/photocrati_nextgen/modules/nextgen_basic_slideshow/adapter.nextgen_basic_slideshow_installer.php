<?php

class A_NextGen_Basic_Slideshow_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_nextgen_basic_slideshow'
		);
	}

	function install_nextgen_basic_slideshow()
	{
		$this->object->install_display_type(
			NEXTGEN_GALLERY_BASIC_SLIDESHOW, array(
				'title'					=>	'NextGEN Basic Slideshow',
				'entity_types'			=>	array('image'),
				'default_source'		=>	'galleries',
				'preview_image_relpath'	=>	'nextgen_basic_slideshow#preview.gif'
			)
		);
	}
}