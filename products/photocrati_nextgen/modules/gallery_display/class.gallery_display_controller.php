<?php

/**
 * A Controller which displays the settings form for the display type, as
 * well as the front-end display
 */
class C_Display_Type_Controller extends C_MVC_Controller
{
	/**
	 * The associated display type with the controller
	 * @var C_Display_Type|stdClass
	 */
	var $_display_type;

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Display_Type_Controller');
		$this->implements('I_Display_Type_Controller');
	}

	function initialize($display_type, $context=FALSE)
	{
		parent::initialize($context);
		$this->_display_type = $display_type;
	}


	/**
	 * Provides default behavior for rendering fields
	 * @param string $method
	 * @param array $args
	 */
	function __call($method, $args)
	{
		if (preg_match("/render_([\w_]+)/", $method, $matches) && !$this->has_method($method)) {
			$field_name = $matches[1];
			$value = isset($this->_display_type->$field_name) ?
				$this->_display_type->$field_name : '';
			return $this->render_partial($field_name, array(
				'value' => $value, 'context' => $this->_display_type->context), TRUE
			);
		}
		else {
			return parent::__call($method, $args);
		}
	}
}

/**
 * Provides instance methods for the C_Display_Type_Controller class
 */
class Mixin_Display_Type_Controller extends Mixin
{
	/**
	 * Renders the frontend display of the display type
	 */
	function index()
	{
		$this->object->render_partial('index');
	}

	/**
	 * Renders the settings form for the display type
	 */
	function settings()
	{
		// Get the fields for this gallery type
		$fields = array();
		foreach ($this->object->get_field_names() as $field) {
			$render_method = "render_{$field}_field";
			if ($this->object->has_method($render_method))
				$fields[] = $this->object->$render_method();
		}

		// Render the display type settings template
		$this->object->render_partial('display_type_settings', array(
			'fields' => $fields
		));
	}

	/**
	 * Returns the name of the fields to
	 */
	function get_field_names()
	{
		return array();
	}
}