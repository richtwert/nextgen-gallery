<?php

class C_Gallery_Image_Mapper extends C_DataMapper
{
    public static $_instances = array();

	function define($context=FALSE)
	{
		parent::define('ngg_pictures', array('attachment', $context));
		$this->get_wrapped_instance()->add_mixin('Mixin_Gallery_Image_Mapper');
		$this->get_wrapped_instance()->add_post_hook(
			'_convert_to_entity',
			'Unserialize Metadata',
			'Hook_Unserialize_Image_Metadata',
			'unserialize_metadata'
		);
		$this->implement('I_Gallery_Image_Mapper');
	}

	function initialize($context=FALSE)
	{
		parent::initialize($context);
		$this->set_model_factory_method('gallery_image');
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

/**
 * Sets the alttext property as the post title
 */
class Mixin_Gallery_Image_Mapper extends Mixin
{
	function get_post_title($entity)
	{
		return $entity->alttext;
	}
}

/**
 * Unserializes the metadata when fetched from the database
 */
class Hook_Unserialize_Image_Metadata extends Hook
{
	function unserialize_metadata($entity)
	{
		if (isset($entity->meta_data) && is_string($entity->meta_data)) {
			$entity->meta_data = $this->object->unserialize($entity->meta_data);
		}
	}
}