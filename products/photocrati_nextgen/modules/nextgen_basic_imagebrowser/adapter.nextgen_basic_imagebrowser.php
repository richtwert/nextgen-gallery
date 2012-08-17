<?php

/**
 * Adds validation for the NextGen Basic ImageBrowser display type
 */
class A_NextGen_Basic_ImageBrowser extends Mixin
{
	function initialize()
	{
		if ($this->object->name == PHOTOCRATI_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER) {
			$this->object->add_pre_hook(
				'validation',
				get_class(),
				'Hook_NextGen_Basic_ImageBrowser_Validation'
			);
			$this->object->add_pre_hook(
				'set_defaults',
				get_class(),
				'Hook_NextGen_Basic_ImageBrowser_Validation'
			);
		}
	}
}

/**
 * Provides validation for the NextGen Basic ImageBrowser display type
 */
class Hook_NextGen_Basic_ImageBrowser_Validation extends Hook
{
	function set_defaults()
	{
		if (!isset($this->object->settings['template']))
			$this->object->settings['template'] = '';
	}

	function validation()
	{

	}
}