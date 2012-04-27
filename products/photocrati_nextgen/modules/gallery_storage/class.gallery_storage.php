<?php

class Mixin_GalleryStorage extends Mixin
{
	/**
	 * Returns the name of the class which provides the gallerystorage
	 * implementation
	 * @return string
	 */
	function _get_gallerystorage_implementation_factory_method()
	{
		$factory_method = '';

		if (!defined('GALLERYSTORAGE_IMPLEMENTATION')) {
			$factory_method = get_option(PHOTOCRATI_GALLERY_OPTION_PREFIX.'gallerystorage_implementation');
			define('GALLERYSTORAGE_IMPLEMENTATION', $factory_method);
		}
		else $factory_method = GALLERYSTORAGE_IMPLEMENTATION;

		$factory_method = 'ngglegacy_gallery_storage';

		return $factory_method;
	}
}

class C_Gallery_Storage extends C_Component
{
	function define($object_name, $context=FALSE)
	{
		$this->add_mixin('Mixin_GalleryStorage');
		$this->wrap('I_GalleryStorage_Driver', array(&$this, '_get_gallerystorage_implementation'), array($object_name, $context));
		$this->implement('I_Gallery_Storage');
	}

	/**
	 * Returns the implementation for the gallerystorage
	 * @param array $args
	 * @return mixed
	 */
	function _get_gallerystorage_implementation($args)
	{
		$object_name = $args[0];
		$context = $args[1];
		$factory_method = $this->_get_gallerystorage_implementation_factory_method();
		$factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
		return $factory->create($factory_method, $object_name, $context);
	}
}