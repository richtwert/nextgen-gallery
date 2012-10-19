<?php

/**
 * Provides the "Display Tab" for the Attach To Post interface/controller
 */
class Mixin_Attach_To_Post_Display_Tab extends Mixin
{
	/**
	 * Renders the JS required for the Backbone-based Display Tab
	 */
	function display_tab_js_action()
	{
		if ($this->object->_validate_request()){
			$this->object->set_content_type('javascript');

			// Get all entities used by the display tab
			$gallery_mapper		= $this->get_registry()->get_utility('I_Gallery_Mapper');
			$album_mapper		= $this->get_registry()->get_utility('I_Album_Mapper');
			$display_type_mapper= $this->get_registry()->get_utility('I_Display_Type_Mapper');
			$display_types		= $display_type_mapper->find_all();

			// Get the nextgen tags
			global $wpdb;
			$tags = $wpdb->get_results(
					"SELECT DISTINCT name AS 'id', name FROM {$wpdb->terms}
					WHERE term_id IN (
						SELECT term_id FROM {$wpdb->term_taxonomy}
						WHERE taxonomy = 'ngg_tag'
					)");

			$this->object->render_view('display_tab_js', array(
				'displayed_gallery'		=>	json_encode($this->object->_displayed_gallery->get_entity()),
				'sources'				=>	json_encode($this->object->get_entity_sources()),
				'gallery_primary_key'	=>	$gallery_mapper->get_primary_key_column(),
				'galleries'				=>	json_encode($gallery_mapper->find_all()),
				'albums'				=>	json_encode($album_mapper->find_all()),
				'tags'					=>	json_encode($tags),
				'display_types'			=>	json_encode($display_type_mapper->find_all()),
			));
		}
	}

	/**
	 * Returns a list of sources to select in the Display Tab
	 */
	function get_entity_sources()
	{
		$retval = array();

		$sources = array(
			array(
				'name'			=>	'galleries',
				'title'			=>	'Galleries',
				'returns'		=>	array('images')
			),

			array(
				'name'			=>	'albums',
				'title'			=>	'Albums',
				'returns'		=>	array('galleries', 'albums')
			),

			array(
				'name'			=>	'tags',
				'title'			=>	'Tags',
				'returns'		=>	array('images')
			),

			array(
				'name'			=>	'random_images',
				'title'			=>	'Random Images',
				'returns'		=>	array('images')
			),

			array(
				'name'			=>	'recent_images',
				'title'			=>	'Recent Images',
				'returns'		=>	array('images')
			)
		);

		foreach ($sources as &$source) {
			$source['value'] = $source['id'] = $source['name'];
			$source['selected'] = $this->object->_displayed_gallery->source == $source['name'];
		}

		return $sources;
	}


	/**
	 * Gets a list of tabs to render for the "Display" tab
	 */
	function _get_display_tabs()
	{
		return array(
			$this->object->_render_display_source_tab(),
			$this->object->_render_display_types_tab(),
			$this->object->_render_display_settings_tab(),
			$this->object->_render_preview_tab()
		);
	}


	/**
	 * Renders the accordion tab, "What would you like to display?"
	 */
	function _render_display_source_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'source_tab',
			'title'		=>	_('What would you like to display?'),
			'content'	=>	$this->object->_render_display_source_tab_contents()
		), TRUE);
	}


	/**
	 * Renders the contents of the source tab
	 * @return string
	 */
	function _render_display_source_tab_contents()
	{
		return $this->object->render_partial('display_tab_source', array(),TRUE);
	}


	/**
	 * Renders the accordion tab for selecting a display type
	 * @return string
	 */
	function _render_display_types_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'display_type_tab',
			'title'		=>	_('Select a display type'),
			'content'	=>	$this->object->_render_display_type_tab_contents()
		), TRUE);
	}


	/**
	 * Renders the contents of the display type tab
	 */
	function _render_display_type_tab_contents()
	{
		return $this->object->render_partial('display_tab_type', array(), TRUE);
	}


	/**
	 * Renders the display settings tab for the Attach to Post interface
	 * @return type
	 */
	function _render_display_settings_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'display_settings_tab',
			'title'		=>	_('Customize the display settings'),
			'content'	=>	$this->object->_render_display_settings_contents()
		), TRUE);
	}

	/**
	 * If editing an existing displayed gallery, retrieves the name
	 * of the display type
	 * @return string
	 */
	function _get_selected_display_type_name()
	{
		$retval = '';

		if ($this->object->_displayed_gallery)
			$retval = $this->object->_displayed_gallery->display_type;

		return $retval;
	}


	/**
	 * Is the displayed gallery that's being edited using the specified display
	 * type?
	 * @param string $name	name of the display type
	 * @return bool
	 */
	function is_displayed_gallery_using_display_type($name)
	{
		$retval = FALSE;

		if ($this->object->_displayed_gallery) {
			$retval = $this->object->_displayed_gallery->display_type == $name;
		}

		return $retval;
	}


	/**
	 * Renders the contents of the display settings tab
	 * @return string
	 */
	function _render_display_settings_contents()
	{
		$retval = array();
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		foreach ($mapper->find_all(array()) as $display_type) {

			// Get the display type controller
			$display_type_controller = $this->object->get_registry()->get_utility(
				'I_Display_Type_Controller', $display_type->name
			);

			// Determine which classes to use for the form's "class" attribute
			$current = $this->object->is_displayed_gallery_using_display_type($display_type->name);
			$css_class =  $current ? 'display_settings_form' : 'display_settings_form hidden';

			// Override the display type settings with that of the displayed
			// gallery
			if ($current) {
				$display_type->settings = $this->array_merge_assoc(
					$display_type->settings,
					$this->object->_displayed_gallery->display_settings,
					TRUE
				);
			}

			$retval[] = $this->object->render_partial('display_settings_form', array(
				'settings'				=>	$display_type_controller->settings_action(
												$display_type, TRUE
											),
				'display_type_name'		=>	$display_type->name,
				'css_class'				=>	$css_class
			), TRUE);

		}

		// Render the default "no display type selected" view
		$css_class = $this->object->_get_selected_display_type_name() ?
			'display_settings_form hidden' : 'display_settings_form';
		$retval[] = $this->object->render_partial('no_display_type_selected', array(
			'no_display_type_selected'	=>	_('No display type selected'),
			'css_class'					=>	$css_class

		), TRUE);

		return implode("\n", $retval);
	}


	/**
	 * Renders the tab used to preview included images
	 * @return string
	 */
	function _render_preview_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'preview_tab',
			'title'		=>	_('Sort or Exclude Images'),
			'content'	=>	$this->object->_render_preview_tab_contents()
		), TRUE);
	}


	/**
	 * Renders the contents of the "Preview" tab.
	 * @return string
	 */
	function _render_preview_tab_contents()
	{
		return $this->object->render_partial('preview_tab', array(), TRUE);
	}


	/**
	 * Retrieves a list of sources for images/galleries
	 */
	function get_sources()
	{
		return array(
			array(
                'id'	        =>	'galleries',
                'title'	        =>	'Galleries',
                'display_type'  =>  'gallery'
            ),
			array(
                'id'	        =>	'albums',
                'title'	        =>	'Albums',
                'display_type'  =>  'album'
            ),
			array(
                'id'	        =>	'image_tags',
                'title'	        =>	'Image Tags',
                'display_type'  =>  'gallery'
            ),
			array(
                'id'	        =>	'recent_images',
                'title'         =>	'Recent Images',
                'display_type'  =>  'gallery'
            ),
			array(
                'id'	        =>	'random_images',
                'title'	        =>	'Random Images',
                'display_type'  =>  'gallery'
            )
		);
	}

	/**
	 * Gets the Handlebar templates for each source
	 */
	function _get_source_templates()
	{
		$retval = array();
		foreach ($this->object->get_sources() as $source) {
			$retval[] = $this->object->call_method('_render_'.$source['id'].'_source_template');
		}
		return $retval;
	}


	/**
	 * Renders the Handlebars template for the "Galleries" source
	 */
	function _render_galleries_source_template()
	{
		return $this->object->render_partial('galleries_source', array(
			'template_name'				=>	'galleries_source_view',
			'existing_galleries_label'	=>	_('Galleries:'),
		), TRUE);
	}


    function _render_albums_source_template()
    {
        return $this->object->render_partial('albums_source', array(
            'template_name'             =>  'albums_source_view',
            'existing_albums_label'     =>  _('Albums:'),
        ), TRUE);
    }


	/**
	 * Renders the Handlebars template for the image tags source
	 * @return string
	 */
	function _render_image_tags_source_template()
	{
		return $this->object->render_partial('image_tags_source', array(
			'template_name'				=>	'image_tags_source_view',
			'tags_label'				=>	_('Tags'),
		), TRUE);
	}
}