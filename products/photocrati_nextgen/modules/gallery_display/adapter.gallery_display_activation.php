<?php

class A_Gallery_Display_Activation extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'Install Displayed Gallery Sources',
			get_class($this),
			'install_displayed_gallery_sources'
		);
	}

	function install_displayed_gallery_sources()
	{
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Source_Mapper');

		// Save "Galleries" source
		$source = $mapper->find_by_name('galleries');
		if (!$source) $source = new stdClass();
		$source->name			= 'galleries';
		$source->title			= 'Galleries';
		$source->returns		= array('image');
		$source->aliases		= array('gallery', 'images', 'image');
		$mapper->save($source);

		// Save "Albums" source
		$source = $mapper->find_by_name('albums');
		if (!$source) $source = new stdClass();
		$source->name		= 'albums';
		$source->title		= 'Albums';
		$source->returns	= array('gallery', 'album');
		$source->aliases	= array('album');
		$mapper->save($source);

		// Save "Tags" source
		$source = $mapper->find_by_name('tags');
		if (!$source) $source = new stdClass();
		$source->name		= 'tags';
		$source->title		= 'Tags';
		$source->returns	= array('image');
		$source->aliases	= array('tag', 'image_tag', 'image_tags');
		$mapper->save($source);

		// Save "Random Images" source
		$source = $mapper->find_by_name('random_images');
		if (!$source) $source = new stdClass();
		$source->name		= 'random_images';
		$source->title		= 'Random Images';
		$source->returns	= array('image');
		$source->aliases	= array('random', 'random_image');
		$mapper->save($source);

		// Save "Recent Images" source
		$source = $mapper->find_by_name('recent_images');
		if (!$source) $source = new stdClass();
		$source->name		= 'recent_images';
		$source->title		= 'Recent Images';
		$source->returns	= array('image');
		$source->aliases	= array('recent', 'recent_image');
		$mapper->save($source);
	}
}