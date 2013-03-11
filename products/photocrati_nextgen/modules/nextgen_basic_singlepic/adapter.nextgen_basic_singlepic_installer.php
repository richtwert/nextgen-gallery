<?php

class A_NextGen_Basic_SinglePic_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class(),
			get_class(),
			'install_singlepic'
		);
	}

	function install_singlepic()
	{
		$this->object->install_display_type(
			NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME, array(
			'title'					=>	'NextGEN Basic SinglePic',
			'entity_types'			=>	array('image'),
			'preview_image_relpath'	=>	'nextgen_basic_singlepic#preview.gif',
			'default_source'		=>	'galleries'
		));
	}
}