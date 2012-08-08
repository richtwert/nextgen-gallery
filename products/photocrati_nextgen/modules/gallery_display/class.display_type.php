<?php

/**
 * A Display Type is a component which renders a collection of images
 * in a "gallery".
 *
 * Properties:
 * - entity_type (gallery, album)
 * - name		 (nextgen_basic-thumbnails)
 * - title		 (NextGEN Basic Thumbnails)
 */
class C_Display_Type extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Displayed_Gallery_Mapper';

	function define()
	{
		parent::define();
		$this->implements('I_Display_Type');
	}

	/**
	 * Initializes a display type with properties
	 * @param array|stdClass|C_Display_Type $properties
	 * @param FALSE|C_Display_Type_Mapper $mapper
	 * @param FALSE|string|array $context
	 */
	function initialize($properties=array(), $mapper=FALSE, $context=FALSE)
	{
		// If no mapper was specified, then get the mapper
		if ($mapper) $mapper = $this->_get_registry()->get_utility($this->_mapper_interface);

		parent::initialize($mapper, $properties, $context);
	}
}