<?php

class A_NextGen_Basic_SinglePic_Mapper extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'set_defaults',
			get_class(),
			'Hook_NextGen_Basic_SinglePic_Defaults'
		);
	}
}

class Hook_NextGen_Basic_SinglePic_Defaults extends Hook
{
	function set_defaults($entity)
	{
		if ($entity->name == NEXTGEN_BASIC_SINGLEPIC_MODULE_NAME) {
			$this->object->_set_default_value($entity->settings, 'width', '');
			$this->object->_set_default_value($entity->settings, 'height', '');
			$this->object->_set_default_value($entity->settings, 'mode', '');
			$this->object->_set_default_value($entity->settings, 'display_watermark', 0);
			$this->object->_set_default_value($entity->settings, 'display_reflection', 0);
			$this->object->_set_default_value($entity->settings, 'float', '');
			$this->object->_set_default_value($entity->settings, 'link', '');
			$this->object->_set_default_value($entity->settings, 'quality', 100);
			$this->object->_set_default_value($entity->settings, 'crop', 0);
		}
	}
}