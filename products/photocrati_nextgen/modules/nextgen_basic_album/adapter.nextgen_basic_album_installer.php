<?php

class A_NextGen_Basic_Album_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_nextgen_basic_album'
		);
	}

	function install_nextgen_basic_album($product)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->install_display_type(
			NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM, array(
			'title'					=>	'NextGEN Basic Compact Album',
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'nextgen_basic_album#compact_preview.jpg',
			'default_source'		=>	'albums',
			'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 200
		));

		$this->object->install_display_type(
			NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM, array(
			'title'					=>	'NextGEN Basic Extended Album',
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'nextgen_basic_album#extended_preview.jpg',
			'default_source'		=>	'albums',
			'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 210
		));
	}
}
