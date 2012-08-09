<?php

class A_Gallery_Display_Factory extends Mixin
{
	/**
	 * Instantiates a Display Type
	 * @param array|stdClass|C_DataMapper_Model $properties
	 * @param C_DataMapper|FALSE $mapper
	 * @param string|array|FALSE $context
	 */
	function display_type($properties=array(), $mapper=FALSE, $context=FALSE)
	{
		return new C_Display_Type($properties, $mapper, $context);
	}

	/**
	 * Instantiates a Displayed Gallery
	 * @param array|stdClass|C_DataMapper_Model $properties
	 * @param C_DataMapper|FALSE $mapper
	 * @param string|array|FALSE $context
	 */
	function displayed_gallery($properties=array(), $mapper=FALSE, $context=FALSE)
	{
		return new C_Displayed_Gallery($properties, $mapper, $context);
	}
}