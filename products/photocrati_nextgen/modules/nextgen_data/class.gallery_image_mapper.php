<?php

class C_Gallery_Image_Mapper extends C_DataMapper
{
    public static $_instances = array();

	/**
	 * Defines the gallery image mapper
	 * @param type $context
	 */
	function define($context=FALSE)
	{
		// Add 'attachment' context
		if (!is_array($context)) $context = array($context);
		array_push($context, 'attachment');
		parent::define('ngg_pictures', $context);
		$this->get_wrapped_instance()->add_mixin('Mixin_Gallery_Image_Mapper');
		$this->get_wrapped_instance()->add_post_hook(
			'_convert_to_entity',
			'Unserialize Metadata',
			'Hook_Unserialize_Image_Metadata',
			'unserialize_metadata'
		);
		$this->implement('I_Gallery_Image_Mapper');
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

    /**
     * Update or add meta data for an image
     *
     * @param int $id The image ID
     * @param array $values An array with existing or new values
     * @return bool result of query
     */
    function update_image_meta($id, $new_values)
    {
        // may not be necessary after all
        // $old_values = unserialize($old_values);
        // $meta = array_merge((array)$old_values, (array)$new_values);
        // $image->meta_data = serialize($meta);
        // return $result;
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
