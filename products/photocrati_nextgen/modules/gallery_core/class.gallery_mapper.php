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
}

class C_Gallery_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_gallery', array('gallery', $context));
		$this->set_model_factory_method('gallery');
		$this->implement('I_Gallery_Mapper');
	}

	function initialize()
	{
		$this->_post_title_field = 'title';
	}
}