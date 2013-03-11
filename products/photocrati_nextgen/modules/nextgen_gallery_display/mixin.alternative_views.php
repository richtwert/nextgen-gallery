<?php

class Mixin_Alternative_Views extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'index_action',
			get_class(),
			get_class(),
			'init_alternative_index'
		);
	}

	/**
	 * Pre hook for the index_action method. If it detects that we're to
	 * display an alternative view, the index_action is never executed
	 * @param C_Displayed_Gallery $displayed_gallery
	 * @param string $return
	 */
	function init_alternative_index($displayed_gallery, $return)
	{
		$this->object->set_alternative_view_links($displayed_gallery);
		if ($this->object->is_alternative_view_request($displayed_gallery->get_display_type())) {
			$this->object->set_method_property(
				'index_action', ExtensibleObject::METHOD_PROPERTY_RUN, FALSE
			);
			$this->object->set_method_property(
				'index_action',
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				$this->object->alternative_index($displayed_gallery, $return)

			);
		}
	}


	/**
	 * Gets alternative views available, by returning a URI segment to match
	 * and and asociated display type
	 */
	function _get_alternative_views($current_display_type)
	{
		$retval = array();

		// Add each existing display type
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		foreach ($mapper->find_by_entity_type($current_display_type->entity_types) as $display_type) {

			// Skip the current display type
			if ($current_display_type->name == $display_type->name) continue;

			// Add any other supported display type as an alternative view
			$retval[$display_type->name] = array(
				'type'	=>	'display_type',
				'name'	=>	$display_type->name,
				'title'	=>	$display_type->title
			);
		}

		return $retval;
	}


	/**
	 * Determines if the request is asking for an alternative view to be
	 * displayed
	 * @return boolean
	 */
	function is_alternative_view_request($display_type)
	{
		$retval = FALSE;

		// Let the request determine what display type or alternative view to render
		if (($show = $this->object->param('show'))) {
			$retval = $this->object->_get_alternative_view($show, $display_type);
		}
		elseif (isset($_SERVER['NGGALLERY']) && (($show = $_SERVER['NGGALLERY']))) {
			$retval = $this->object->_get_alternative_view($show, $display_type);
		}

		return $retval;
	}


	/**
	 * Determines if the controller is actually being used to serve an
	 * alternate view
	 * @param string $display_type_name
	 */
	function is_serving_alternative_view($display_type)
	{
		$retval = FALSE;
		if (($view = $this->object->is_alternative_view_request($display_type))) {
			$retval = ($view['type'] == 'display_type' && $display_type->name != $view['name']);
		}
		return $retval;
	}

	/**
	 * Gets the view associated with a uri segment
	 * @param string $uri_segment
	 * @return array
	 */
	function _get_alternative_view($uri_segment, $current_display_type)
	{
		$views = $this->object->_get_alternative_views($current_display_type);
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

		if (($view = $this->object->_get_alternative_view($uri_segment, $displayed_gallery->get_display_type())))
        {
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

			// Render!
			$controller->enqueue_frontend_resources($displayed_gallery);
			$retval = $controller->index_action($displayed_gallery, $return);
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
		// TODO - Move this function to the M_Gallery_Display class, so that it gets executed higher up in the stack,
        // avoiding unnecessary execution of other things
		$retval	= FALSE;

        // Let the request determine what display type or alternative view to render
		if (($show = $this->object->param('show', $displayed_gallery->id()))) {
			$retval = $this->object->_render_alternative_view($displayed_gallery, $show, $return);
		}
		elseif (isset($_SERVER['NGGALLERY']) && (($show = $_SERVER['NGGALLERY']))) {
			$retval = $this->object->_render_alternative_view($displayed_gallery, $show, $return);
		}

		if (!$return && $retval)
            echo $retval;

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
		$params								= &$displayed_gallery->display_settings;
		$params['alternative_view_link_url']= '';
		$params['alternative_view_link']	= '';
		$params['return_link_url']			= '';
		$params['return_link']				= '';
		$current_url						= $this->object->get_routed_url(TRUE);

		// Add show alternative view link
        if ($params['show_alternative_view_link'] && $params['alternative_view'])
        {
			$url = $this->object->set_param_for($current_url, 'show', $params['alternative_view'], $displayed_gallery->id());;
			$params['alternative_view_link_url'] = $url;
			$params['alternative_view_link'] = "<a href='".esc_attr($url)."'>".
					htmlentities($params['alternative_view_link_text']).
				'</a>';
		}

		// If we're serving an alternative view, then we'll need to add
		// a return link
		if ($this->object->is_serving_alternative_view($displayed_gallery->get_display_type()) && $params['show_return_link']) {
			$url = $this->object->remove_param_for($current_url, 'show', $displayed_gallery->id());
			$params['return_link_url'] = $url;
			$params['return_link'] =
				"<a href='".esc_attr($url)."'>".
					htmlentities($params['return_link_text']).
				"</a>";
		}
		return $displayed_gallery;
	}
}