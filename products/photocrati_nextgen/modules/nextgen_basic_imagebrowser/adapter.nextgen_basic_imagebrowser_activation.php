<?php

/**
 * Adds an activation routine for NextGen Basic ImageBrowser
 */
class A_NextGen_Basic_ImageBrowser_Activation extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'NextGen Basic ImageBrowser - Activation',
			get_class($this),
			'install_nextgen_basic_imagebrowser'
		);
	}

	/**
	 * Installs the NextGen Basic ImageBrowser display type
	 */
	function install_nextgen_basic_imagebrowser()
	{
		$mapper				= $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		$display_type		= $mapper->find_by_name(NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER);
		if (!$display_type) {
			$display_type	= new stdClass();
		}$display_type->name				 = NEXTGEN_GALLERY_NEXTGEN_BASIC_IMAGEBROWSER;
		$display_type->title				 = "NextGEN Basic ImageBrowser";
		$display_type->entity_types			 = array('image');
		$display_type->preview_image_relpath = $this->find_static_file('preview.gif', TRUE);
    $display_type->default_source       = 'galleries';
		$mapper->save($display_type);
		unset($mapper);
	}
}
