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

	function install_nextgen_basic_album()
	{
		$this->object->install_display_type(
			NEXTGEN_GALLERY_NEXTGEN_BASIC_COMPACT_ALBUM, array(
			'title'					=>	'NextGEN Basic Compact Album',
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'nextgen_basic_album#compact_preview.gif',
			'default_source'		=>	'albums'
		));

		$this->object->install_display_type(
			NEXTGEN_GALLERY_NEXTGEN_BASIC_EXTENDED_ALBUM, array(
			'title'					=>	'NextGEN Basic Compact Album',
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'nextgen_basic_album#compact_preview.gif',
			'default_source'		=>	'albums'
		));
	}
}