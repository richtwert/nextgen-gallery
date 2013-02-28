<?php

class A_NextGen_Basic_Thumbnails_Activation extends Mixin
{
	/**
	 * Adds the activation routine
	 */
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_nextgen_basic_thumbnails'
		);
	}

	/**
	 * Installs the display type for NextGEN Basic Thumbnails
	 */
	function install_nextgen_basic_thumbnails()
	{
		$this->object->install_display_type(NEXTGEN_GALLERY_BASIC_THUMBNAILS,
		array(
			'title'					=>	'NextGEN Basic Thumbnails',
			'entity_types'			=>	array('image'),
			'preview_image_relpath'	=>	'nextgen_basic_thumbnails#preview.gif',
			'default_source'		=>	'galleries'
		));
	}
}
