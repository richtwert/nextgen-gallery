<?php

class Mixin_Display_Type_Form extends Mixin
{
	/**
	 * Returns the name of the display type. Sub-class should override
	 * @throws Exception
	 * @returns string
	 */
	function get_display_type_name()
	{
		throw new Exception(__METHOD__." not implemented");
	}

	/**
	 * Returns the model (display type) used in the form
	 * @return stdClass
	 */
	function get_model()
	{
		$mapper = $this->get_registry()->get_utility('I_Display_Type_Mapper');
		return $mapper->find_by_name($this->object->get_display_type_name());
	}

	/**
	 * Returns the title of the form, which is the title of the display type
	 * @returns string
	 */
	function get_title()
	{
		return $this->object->get_model()->title;
	}
}