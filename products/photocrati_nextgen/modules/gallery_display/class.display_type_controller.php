<?php

/**
 * A Controller which displays the settings form for the display type, as
 * well as the front-end display
 */
class C_Display_Type_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Display_Type_Controller');
		$this->implement('I_Display_Type_Controller');
	}

	function initialize($context=FALSE)
	{
		parent::initialize($context);

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


	/**
	 * Gets a singleton of the mapper
	 * @param string|array $context
	 * @return C_Display_Type_Controller
	 */
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Display_Type_Controller($context);
        }
        return self::$_instances[$context];
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
	function index($display_type, $return=FALSE)
	{
		return $this->object->render_partial('index', array(), $return);
	}

	/**
	 * This method should be overwritten by other adapters/mixins, and call
	 * wp_enqueue_script() / wp_enqueue_style()
	 */
	function enqueue_resources()
	{
	}

	/**
	 * Renders the settings form for the display type
	 */
	function settings($display_type, $return)
	{
		// Get the fields for this gallery type
		$fields = array();
		foreach ($this->object->get_field_names($display_type) as $field) {
			$render_method = "render_{$field}_field";
			if ($this->object->has_method($render_method))
				$fields[] = $this->object->$render_method();
		}

		// Render the display type settings template
		return $this->object->render_partial('display_type_settings', array(
			'fields'		=> $fields,
			'display_type'	=> $display_type
		), $return);
	}

	/**
	 * Returns the name of the fields to
	 */
	function get_field_names($display_type)
	{
		return array();
	}
}