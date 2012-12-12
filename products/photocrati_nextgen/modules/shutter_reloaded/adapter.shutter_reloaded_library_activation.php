<?php

class A_Shutter_Reloaded_Library_Activation extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'Shutter Reloaded Library - Activation',
			get_class($this),
			'install_shutter_reloaded_library'
		);
	}


	function install_shutter_reloaded_library()
	{
		$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$mapper->save((object)array(
			'name'				=>	'shutter 2.0.1',
			'code'				=>	'class="shutterset_%GALLERY_NAME%"',
			'css_stylesheets'	=>	PHOTOCRATI_GALLERY_PLUGIN_MODULE_URL.'/'.basename(dirname(__FILE__)).'/static/shutter/shutter.css',
			'scripts'			=>	PHOTOCRATI_GALLERY_PLUGIN_MODULE_URL.'/'.basename(dirname(__FILE__)).'/static/shutter/shutter.js'."\n".
									PHOTOCRATI_GALLERY_PLUGIN_MODULE_URL.'/'.basename(dirname(__FILE__)).'/static/nextgen_shutter_reloaded.js'
		));
	}
}
