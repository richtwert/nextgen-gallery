<?php

class Hook_Unserialize_Image_Metadata extends Hook
{
	function unserialize_metadata($entity)
	{
		if (isset($entity->meta_data) && is_string($entity->meta_data)) {
			$entity->meta_data = $this->object->unserialize($entity->meta_data);
		}
	}
}

class C_Gallery_Image_Mapper extends C_DataMapper
{
    public static $_instances = array();

	function define($context=FALSE)
	{
		parent::define('ngg_pictures', array('attachment', $context));
		$this->set_model_factory_method('gallery_image');
		$this->_wrapped_instance->add_post_hook(
			'_convert_to_entity',
			'Unserialize Metadata',
			'Hook_Unserialize_Image_Metadata',
			'unserialize_metadata'
		);
	}


	function initialize($context=FALSE)
	{
		parent::initialize($context);

		// Tells the CustomPost driver (when used) what property to use
		// as the value for the "post_title" column
		$this->_post_title_field = 'alttext';
	}

    static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Gallery_Image_Mapper($context);
        }
        return self::$_instances[$context];
    }
}
