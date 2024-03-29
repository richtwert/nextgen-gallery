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


    function _save_entity($entity)
    {
        $retval = $this->call_parent('_save_entity', $entity);

        if ($retval) {
            do_action('ngg_created_new_gallery', $entity->{$entity->id_field});
        }

        return $retval;
    }

    function set_preview_image($gallery, $image, $only_if_empty=FALSE)
    {
        $retval = FALSE;

        // We need the gallery object
        if (is_numeric($gallery)) {
            $gallery = $this->object->find($gallery);
        }

        // We need the image id
        if (!is_numeric($image)) {
            if (method_exists($image, 'id')) {
                $image = $image->id();
            }
            else {
                $image = $image->{$image->id_field};
            }
        }

        if ($gallery && $image) {
            if (($only_if_empty && !$gallery->previewpic) OR !$only_if_empty) {
                $gallery->previewpic = $image;
                $retval = $this->object->save($gallery);
            }
        }

        return $retval;
    }

	/**
	 * Sets default values for the gallery
	 */
	function set_defaults($entity)
	{
		// If author is missing, then set to the current user id
        // TODO: Using wordpress function. Should use abstraction
		$this->object->_set_default_value($entity, 'author', get_current_user_id());
	}
}