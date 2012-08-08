<?php

/**
 * Associates a Display Type with a collection of images
 *
 * * Properties:
 * - source				(gallery, album, recent_images, random_images, etc)
 * - container_ids		(gallery ids, album ids, tag ids, etc)
 * - display_type		(name of the display type being used)
 * - display_settings	(settings for the display type)
 * - exclusions			(excluded entity ids)
 */
class C_Displayed_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Displayed_Gallery_Mapper';

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Displayed_Gallery_Validation');
		$this->implement('I_Displayed_Gallery');
	}


	/**
	 * Initializes a display type with properties
	 * @param array|stdClass|C_Displayed_Gallery $properties
	 * @param FALSE|C_Displayed_Gallery_Mapper $mapper
	 * @param FALSE|string|array $context
	 */
	function initialize($properties=array(), $mapper=FALSE, $context=FALSE)
	{
		if (!$mapper) $mapper = $this->_get_registry()->get_utility($this->_mapper_interface);

		parent::initialize($mapper, $properties, $context);
	}
}

/**
 * Provides validation
 */
class Mixin_Displayed_Gallery_Validation extends Mixin
{
	function validate()
	{
		$this->object->validates_presense_of('source', 'gallery_type');
		if (in_array($this->object->source, array('galleries', 'albums', 'tags'))) {
			$this->object->validates_presence_of('container_ids');
		}

		// We need to somehow check that the display type exists. Probably,
		// the thing to do is create a validates_existence_of() method
		// that uses the datamapper class
	}
}