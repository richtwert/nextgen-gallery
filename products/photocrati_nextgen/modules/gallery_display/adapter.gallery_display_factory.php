<?php

class A_Gallery_Display_Factory extends Mixin
{
	/**
	 * Instantiates a Display Type
	 * @param array|stdClass|C_DataMapper_Model $properties
	 * @param C_DataMapper|FALSE $mapper
	 * @param string|array|FALSE $context
	 */
	function display_type($mapper=FALSE, $properties=array(), $context=FALSE)
	{
		return new C_Display_Type($mapper, $properties, $context);
	}

	/**
	 * Instantiates a Displayed Gallery
	 * @param array|stdClass|C_DataMapper_Model $properties
	 * @param C_DataMapper|FALSE $mapper
	 * @param string|array|FALSE $context
	 */
	function displayed_gallery($mapper=FALSE, $properties=array(), $context=FALSE)
	{
		return new C_Displayed_Gallery($mapper, $properties, $context);
	}
}