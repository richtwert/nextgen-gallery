<?php

class A_Gallery_Display_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'Install Displayed Gallery Sources',
			get_class($this),
			'install_displayed_gallery_sources'
		);

		$this->object->add_post_hook(
			'uninstall',
			get_class($this).'::Uninstall',
			get_class(),
			'uninstall_nextgen_gallery_display'
		);
	}

	/**
	 * Installs a display type
	 * @param string $name
	 * @param array $properties
	 */
	function install_display_type($name, $properties=array())
	{
		// Try to find the existing entity. If it doesn't exist, we'll create
		$fs					= $this->get_registry()->get_utility('I_Fs');
		$mapper				= $this->get_registry()->get_utility('I_Display_Type_Mapper');
		$display_type		= $mapper->find_by_name($name);
		if (!$display_type)	$display_type = new stdClass;

		// Update the properties of the display type
		$properties['name'] = $name;
		foreach ($properties as $key=>$val) {
			if ($key == 'preview_image_relpath') {
				$val = $fs->find_static_relpath($val);
			}
			$display_type->$key = $val;
		}

		// Save the entity
		$mapper->save($display_type);
		unset($mapper);
	}

	function install_displayed_gallery_source($name, $properties)
	{
		// Try to find the existing source. If not found, then we'll create
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Source_Mapper');
		$source = $mapper->find_by_name($name);
		if (!$source) $source = new stdClass;

		// Update the properties
		foreach ($properties as $key=>$val) $source->$key = $val;
		$source->name = $name;

		// Save!
		$mapper->save($source);
		unset($mapper);
	}

	/**
	 * Deletes all displayed galleries
	 */
	function uninstall_displayed_galleries()
	{
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		$mapper->delete()->run_query();
	}

	/**
	 * Uninstalls all display types
	 */
	function uninstall_display_types()
	{
		$mapper = $this->get_registry()->get_utility('I_Display_Type_Mapper');
		$mapper->delete()->run_query();
	}

	/**
	 * Installs displayed gallery sources
	 */
	function install_displayed_gallery_sources($product)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->install_displayed_gallery_source('galleries', array(
			'title'		=>	'Galleries',
			'returns'	=>	array('image'),
			'aliases'	=>	array('gallery', 'images', 'image')
		));

		$this->object->install_displayed_gallery_source('albums', array(
			'title'		=>	'Albums',
			'returns'	=>	array('gallery', 'album'),
			'aliases'	=>	array('album')
		));

		$this->object->install_displayed_gallery_source('tags', array(
			'title'		=>	'Tags',
			'returns'	=>	array('image'),
			'aliases'	=>	array('tag', 'image_tag', 'image_tags')
		));

		$this->object->install_displayed_gallery_source('random_images', array(
			'title'		=>	'Random Images',
			'returns'	=>	array('image'),
			'aliases'	=>	array('random', 'random_image')
		));

		$this->object->install_displayed_gallery_source('recent_images', array(
			'title'		=>	'Recent images',
			'returns'	=>	array('image'),
			'aliases'	=>	array('recent', 'recent_image')
		));
	}

	/**
	 * Deletes all displayed gallery sources
	 */
	function uninstall_displayed_gallery_sources()
	{
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Source_Mapper');
		$mapper->delete()->run_query();
	}

	/**
	 * Uninstalls this module
	 */
	function uninstall_nextgen_gallery_display($product, $hard = FALSE)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->uninstall_display_types();
		$this->object->uninstall_displayed_gallery_sources();
		if ($hard) $this->object->uninstall_displayed_galleries();
	}


}