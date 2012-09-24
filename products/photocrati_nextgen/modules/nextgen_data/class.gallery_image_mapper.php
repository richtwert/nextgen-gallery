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

	function set_defaults($entity)
	{
		// If not set already, we'll add an exclude property. This is used
		// by NextGEN Gallery itself, as well as the Attach to Post module
		$this->object->_set_default_value($entity, 'exclude', FALSE);

		// Ensure that the object has a description attribute
		$this->object->_set_default_value($entity, 'description', '');

		// If not set already, set a default sortorder
		$this->object->_set_default_value($entity, 'sortorder', 0);

		// The imagedate must be set
		$this->object->_set_default_value($entity, 'imagedate', date("Y-d-m h-i-s"));

		// If a filename is set, and no alttext is set, then set the alttext
		// to the basename of the filename (legacy behavior)
		if ($this->object->filename) {
			$path_parts = pathinfo( $this->object->filename);
			$alttext = ( !isset($path_parts['filename']) ) ?
				substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) :
				$path_parts['filename'];
			$this->object->_set_default_value($alttext);
		}
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
