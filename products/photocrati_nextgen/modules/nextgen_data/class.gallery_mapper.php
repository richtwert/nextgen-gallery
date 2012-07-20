<?php

class Mixin_Gallery_Mapper extends Mixin
{
	/**
	 * Saves a gallery
	 * @param stdClass|int|C_NextGen_Gallery $gallery
	 */
	function save($gallery)
	{
		// TODO: Should this be in a prehook instead of a mixin?
		$retval = FALSE;
		if ( current_user_can(PHOTOCRATI_GALLERY_ADD_GALLERY_CAPABILITY)) {
			$retval = $this->call_parent();
		}
		return $retval;
	}

	/**
	 * Determines whether a user id has permissions to manage this gallery
	 */
	function can_manage_this_gallery($gallery, $user=FALSE)
	{
		// Ensure we have the gallery id
		if (!is_int($gallery)) {
			$pkey = $this->object->get_primary_key_column();
			$gallery = $gallery->$pkey;
		}

		// Ensure we have a user ID to compare
		if (!$user) $user = get_current_user_id ();
		else if (!is_int($user)) {
			$user = $user->ID;
		}

		return user_can($user, PHOTOCRATI_GALLERY_PERM_MANAGE_OTHER_GALLERIES);
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
		$factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
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

class C_Gallery_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_gallery', array('gallery', $context));
		$this->set_model_factory_method('gallery');
		$this->add_mixin('Mixin_Gallery_Mapper');
		$this->implement('I_Gallery_Mapper');
	}

	function initialize()
	{
		$this->_post_title_field = 'title';
	}
}