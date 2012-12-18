<?php

class A_NextGen_Basic_Thumbnails_Alternative_Views extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'_get_alternative_views',
			'NextGen Legacy Slideshow Alternative Views',
			'Hook_NextGen_Basic_Thumbnails_Alternative_Views'
		);
	}
}

class Hook_NextGen_Basic_Thumbnails_Alternative_Views extends Hook
{
	function _get_alternative_views()
	{
		// Get the views
		$views = $this->object->get_method_property(
			$this->method_called, ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// Add our custom views
		if (is_array($views)) {
			$view_info = array(
				'type'	=>	'display_type',
				'name'	=>	'photocrati-nextgen_basic_thumbnails'
			);
			$views['gallery']	= $views['thumbs'] = $views['gallery'] = $view_info;
			$this->object->set_method_property(
				$this->method_called, ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE, $views
			);
		}

		return $views;
	}
}