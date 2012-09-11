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
	 * Enqueues static resources required for lightbox effects
	 * @param type $displayed_gallery
	 */
	function enqueue_lightbox_resources($displayed_gallery)
	{
		// Enqueue the lightbox effect library
		$settings	= $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$mapper		= $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$library	= $mapper->find_by_name($settings->thumbEffect);
		if ($library)
        {
			$i=0;
			foreach (explode("\n", $library->scripts) as $script) {
				wp_enqueue_script(
					$library->name.'-'.$i,
					$script
				);
				if ($i == 0 AND isset($library->values)) {
					foreach ($library->values as $name => $value) {
						$this->object->_add_script_data(
							$library->name . '-0',
							$name,
							$value,
							FALSE
						);
					}
				}
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
	}


	/**
	 * This method should be overwritten by other adapters/mixins, and call
	 * wp_enqueue_script() / wp_enqueue_style()
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		$this->object->enqueue_lightbox_resources($displayed_gallery);

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
		wp_enqueue_style(
			'nextgen_display_settings',
			$this->object->static_url('nextgen_display_settings.css')
		);

		wp_enqueue_script(
			'nextgen_display_settings',
			$this->object->static_url('nextgen_display_settings.js')
		);
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
			'fields'		=> $fields,
		), $return);
	}


	/**
	 * Displays the field used for alternative views
	 */
	function _render_alternative_view_field()
	{
		// TODO: Need to wrap up once Benjamin is finished interface adjustments
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
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$effect_code = $settings->thumbCode;
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


    /**
     * This allows certain conditions to trigger by URL parameter a change of the current display type
     *
     * This is necessary for legacy URL support as well as the "view as slideshow" / "view as gallery" toggle links
     * @param C_Displayed_Gallery $displayed_gallery
     * @return bool|string FALSE if no change was made, TRUE on success
     */
    function alternative_index($displayed_gallery, $return=FALSE)
    {
		// TODO - Move this function to the M_Gallery_Display class, so that it
		// gets executed higher up in the stack, avoiding unnecessary execution
		// of other things
		$retval					= FALSE;
        $original_display_type	= $displayed_gallery->display_type;

        // Let the request determine what display type or alternative view to render
		if (($show = get_query_var('show'))) {
			$retval = $this->_render_alternative_view($displayed_gallery, $show, $return);
		}
		elseif (isset($_SERVER['NGGALLERY']) && (($show = $_SERVER['NGGALLERY']))) {
			$retval = $this->_render_alternative_view($displayed_gallery, $show, $return);
		}

        return $retval;
    }


	/**
	 * Gets the hyperlink for the alternative view link
	 * @param string $params
	 * @return string
	 */
	function set_alternative_view_links($displayed_gallery)
	{
		// Set some defaults
		$params = &$displayed_gallery->display_settings;
		$params['alternative_view_link_url']	= '';
		$params['alternative_view_link']		= '';
		$params['return_link_url']				= '';
		$params['return_link']					= '';

		// Add show alternative view link
		if ($params['show_alternative_view_link']) {
			if (($url = $this->object->get_absolute_url('nggallery/'.$params['show_alternative_view_link']))) {
				$params['alternative_view_link_url'] = $url;
				$params['alternative_view_link'] = "<a href='".esc_attr($url)."'>".
						htmlentities($params['alternative_view_link_text']).
					'</a>';
			}
		}



		// If we're serving an alternative view, then we'll need to add
		// a return link
		if ($this->object->is_serving_alternative_view($displayed_gallery->display_type) && $params['show_return_link']) {
			if (($url = $this->object->get_absolute_url())) {
				$url = remove_query_arg('show', $url);
				$params['return_link_url'] = $url;
				$params['return_link'] =
					"<a href='".esc_attr($url)."'>".
						htmlentities($params['return_link_text']).
					"</a>";
			}
		}
		return $displayed_gallery;
	}


	/**
	 * Gets alternative views available, by returning a URI segment to match
	 * and and asociated display type
	 */
	function _get_alternative_views()
	{
		$retval = array();

		// Add each existing display type
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		foreach ($mapper->find_all() as $display_type) {
			$retval[$display_type->name] = array(
				'type'	=>	'display_type',
				'name'	=>	$display_type->name
			);
		}

		return $retval;
	}

	/**
	 * Determines if the request is asking for an alternative view to be
	 * displayed
	 * @return boolean
	 */
	function is_alternative_view_request()
	{
		$retval = FALSE;

		// Let the request determine what display type or alternative view to render
		if (($show = get_query_var('show'))) {
			$retval = $this->_get_alternative_view($show);
		}
		elseif (isset($_SERVER['NGGALLERY']) && (($show = $_SERVER['NGGALLERY']))) {
			$retval = $this->_get_alternative_view($show);
		}

		return $retval;
	}


	/**
	 * Determines if the controller is actually being used to serve an
	 * alternate view
	 * @param string $display_type_name
	 */
	function is_serving_alternative_view($display_type_name)
	{
		$retval = FALSE;
		if (($view = $this->object->is_alternative_view_request())) {
			$retval = ($view['type'] == 'display_type' && $display_type_name == $view['name']);
		}
		return $retval;
	}

	/**
	 * Gets the view associated with a uri segment
	 * @param string $uri_segment
	 * @return array
	 */
	function _get_alternative_view($uri_segment)
	{
		$views = $this->object->_get_alternative_views();
		return isset($views[$uri_segment]) ? $views[$uri_segment] : NULL;
	}


	/**
	 * Gets the alternative view information for a particular URI segment
	 * @param string $key
	 * @return array
	 */
	function _render_alternative_view($displayed_gallery, $uri_segment, $return)
	{
		$retval = '';
		if (($view = $this->_get_alternative_view($uri_segment))) {

			// We leave room for other alternative view 'types'
			// by letting a method become responsible for displaying
			// the alternative view. Third-party methods just need to
			// add a mixin to the C_Display_Type_Controller class
			// to support their own custom alternative view types.
			$method = "_render_{$view['type']}_alternative_view";
			if ($this->object->has_method($method)) {
				$retval = $this->object->call_method($method, array(
					$displayed_gallery,
					$view,
					$return
				));
			}
		}
		return $retval;
	}


	/**
	 * Renders display types as alternative views
	 * @param C_Displayed_Gallery|stdClass $displayed_gallery
	 * @param array $view_info
	 * @return bool TRUE if an alternative view was rendered
	 */
	function _render_display_type_alternative_view($displayed_gallery, $view_info, $return)
	{
		$retval = '';

		if ($displayed_gallery->display_type != $view_info['name']) {
			$current_display_type_name	= $displayed_gallery->display_type;
			$current_display_settings	= $displayed_gallery->display_settings;

			// Hijack the display type configuration. A request might also
			// include custom display settings
			$displayed_gallery->display_type = $view_info['name'];
			$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
			$display_type = $mapper->find_by_name($displayed_gallery->display_type, TRUE);
			$custom_settings = $this->object->param('alternative_settings', array());
			$displayed_gallery->display_settings = $this->array_merge_assoc(
				$display_type->settings,
				$custom_settings
			);

			// Override the alternative display type's return link;
			$displayed_gallery->display_settings['previous_display_type'] = $current_display_type_name;
			$displayed_gallery->display_settings['return_link_text'] = $current_display_settings['return_link_text'];
			$displayed_gallery->display_settings['return_link_url'] = $current_display_settings['return_link_url'];
			$displayed_gallery->display_settings['return_link'] = $current_display_settings['return_link'];

			// Get the display type controller for the alternative view
			$controller = $this->object->get_registry()->get_utility(
				'I_Display_Type_Controller', $displayed_gallery->display_type
			);
			$controller->set_alternative_view_links($displayed_gallery);

			// Render!
			$controller->enqueue_frontend_resources($displayed_gallery);
			$retval = $controller->index($displayed_gallery, $return);
		}

		return $retval;
	}


}
