<?php

/**
 * Provides a datamapper for galleries
 */
class C_Gallery_Mapper extends C_DataMapper
{
    public static $_instances = array();

	/**
	 * Define the object
	 * @param string $context
	 */
	function define($context=FALSE)
	{
		// Add 'gallery' context
		if (!is_array($context)) $context = array($context);
		array_push($context, 'gallery');

		// Continue defining the object
		parent::define('ngg_gallery', $context);
		$this->set_model_factory_method('gallery');
		$this->get_wrapped_instance()->add_mixin('Mixin_Gallery_Mapper');
		$this->implement('I_Gallery_Mapper');

		$this->_post_title_field = 'title';
	}

	/**
	 * Returns a singleton of the gallery mapper
	 * @param string $context
	 * @return C_Gallery_Mapper
	 */
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Gallery_Mapper($context);
        }
        return self::$_instances[$context];
    }
}

class Mixin_Gallery_Mapper extends Mixin
{
	/**
	 * Uses the title property as the post title when the Custom Post driver
	 * is used
	 */
	function get_post_title($entity)
	{
		return $entity->title;
	}


	/**
	 * Sets the preview image for the gallery
	 * @param int|stdClass|C_NextGen_Gallery $gallery
	 * @return bool
	 */
	function set_gallery_preview_image($gallery)
	{
		$retval = FALSE;

		// Ensure we have the gallery id and gallery entitys
		$gallery_id = $gallery;
		if (!is_int($gallery)) {
			$pkey = $this->object->get_primary_key_column();
			$gallery_id = $gallery->$pkey;
		}
		else {
			$gallery = $this->object->find($gallery_id);
		}

		// Get the first gallery image
		$factory = $this->_get_registry()->get_utility('I_Component_Factory');
		$image_mapper = $factory->create('gallery_image_mapper');
		$image = $image_mapper->find_first(array('galleryid = %s', $gallery));

		// Set preview image for the gallery
		if ($image) {
			$pkey = $image->id_field;
			$gallery->previewpic = $image->$pkey;
			$retval = $this->object->save($gallery);
		}

		return $retval;
	}
}