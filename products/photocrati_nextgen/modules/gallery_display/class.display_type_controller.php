<?php

/**
 * A Controller which displays the settings form for the display type, as
 * well as the front-end display
 */
class C_Display_Type_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Display_Type_Controller');
		$this->implement('I_Display_Type_Controller');
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
    public static function get_instance($context = FALSE)
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
	 * This method should be overwritten by other adapters/mixins, and call
	 * wp_enqueue_script() / wp_enqueue_style()
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		// Enqueue the lightbox effect library
		$settings	= $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$mapper		= $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$library	= $mapper->find_by_name($settings->thumbEffect);
		if ($library) {
			$i=0;
			foreach (explode("\n", $library->scripts) as $script) {
				wp_enqueue_script(
					$library->name.'-'.$i,
					$script
				);
				$i+=1;
			}
			$i=0;
			foreach (explode("\n", $library->css_stylesheets) as $style) {
				wp_enqueue_style(
					$library->name.'-'.$i,
					$style
				);
				$i+=1;
			}
		}

		// Enqueue the display type library
		wp_enqueue_script(
			$displayed_gallery->display_type,
			$this->object->_get_js_lib_url()
		);
		$this->object->_add_script_data(
			$displayed_gallery->display_type,
			'galleries',
			new stdClass()
		);

		// Enqueue the display type initialization routine
		wp_enqueue_script(
			$displayed_gallery->display_type.'_init',
			$this->object->_get_js_init_url(),
			array($displayed_gallery->display_type)
		);
		$this->object->_add_script_data(
			$displayed_gallery->display_type.'_init',
			'galleries.gallery_'.$displayed_gallery->id(),
			(array)$displayed_gallery->get_entity(),
			FALSE
		);
	}

	/**
	 * Enqueues resources for a particular display type
	 * @param C_Display_Type $display_type
	 */
	function enqueue_backend_resources($display_type)
	{
		return;
	}


	/**
	 * Renders the frontend display of the display type
	 */
	function index($display_type, $return=FALSE)
	{
		return $this->object->render_partial('index', array(), $return);
	}


	/**
	 * Renders the settings form for the display type
	 */
	function settings($display_type, $return)
	{
		// Get the fields for this gallery type
		$fields = array();
		foreach ($this->object->_get_field_names() as $field) {
			$render_method = "_render_{$field}_field";
			if ($this->object->has_method($render_method))
				$fields[] = $this->object->$render_method($display_type);
		}

		// Render the display type settings template
		return $this->object->render_partial('display_type_settings', array(
			'title'			=> $display_type->title,
			'fields'		=> $fields,
		), $return);
	}

	/**
	 * Returns the name of the fields to
	 */
	function _get_field_names()
	{
		return array();
	}

	/**
	 * Returns the url for the JavaScript library required
	 * @return null|string
	 */
	function _get_js_lib_url()
	{
		return NULL;
	}

	/**
	 * Returns the url for the JavaScript initialization code required
	 * @return null|string
	 */
	function _get_js_init_url()
	{
		return NULL;
	}


	/**
	 * Returns the effect HTML code for the displayed gallery
	 * @param type $displayed_gallery
	 */
	function get_effect_code($displayed_gallery)
	{
		$effect_code = $displayed_gallery->display_settings['effect_code'];
		$effect_code = str_replace('%GALLERY_ID%', $displayed_gallery->id(), $effect_code);
		$effect_code = str_replace('%GALLERY_NAME%', $displayed_gallery->id(), $effect_code);
		return $effect_code;
	}


	/**
	 * Adds data to the DOM which is then accessible by a script
	 * @param string $handle
	 * @param string $object_name
	 * @param mixed $object_value
	 * @param bool $define
	 */
	function _add_script_data($handle, $object_name, $object_value, $define=TRUE, $override=FALSE)
	{
		$retval = FALSE;

		// wp_localize_script allows you to add data to the DOM, associated
		// with a particular script. You can even call wp_localie_script
		// multiple times to add multiple objects to the DOM. However, there
		// are a few problems with wp_localize_script:
		//
		// - If you call it with the same object_name more than once, you're
		//   overwritting the first call.
		// - You cannot namespace your objects due to the "var" keyword always
		// - being used.
		//
		// To circumvent the above issues, we're going to use the WP_Scripts
		// object to workaround the above issues
		global $wp_scripts;

		// Has the script been registered or enqueued yet?
		if (isset($wp_scripts->registered[$handle])) {

			// Get the associated data with this script
			$script = &$wp_scripts->registered[$handle];
			$data = &$script->extra['data'];

			// Construct the addition
			$addition = $define ? "\nvar {$object_name} = ".json_encode($object_value).';' :
				"\n{$object_name} = ".json_encode($object_value).';';

			// Add the addition
			if ($override) {
				$data .= $addition;
				$retval = TRUE;
			}
			else if (strpos($data, "{$object_name} = ") === FALSE) {
				$data .= $addition;
				$retval = TRUE;
			}
		}

		return $retval;
	}
}