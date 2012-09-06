<?php

class A_NextGen_Basic_Slideshow_Activation extends Mixin
{
	/**
	 * Adds the activation routine
	 */
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'NextGEN Basic Slideshow - Activation',
			get_class($this),
			'install_nextgen_basic_slideshow'
		);
	}

	/**
	 * Installs the display type for NextGEN Basic Thumbnails
	 */
	function install_nextgen_basic_slideshow()
	{
		$mapper		= $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		$display_type = $mapper->find_by_name(PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW, TRUE);
		if (!$display_type) {
			$display_type = new stdClass();
		}
		$display_type->name = PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW;
		$display_type->title = "NextGEN Basic Slideshow";
		$display_type->entity_type = 'gallery';
		$mapper->save($display_type);
		unset($mapper);
	}
}
