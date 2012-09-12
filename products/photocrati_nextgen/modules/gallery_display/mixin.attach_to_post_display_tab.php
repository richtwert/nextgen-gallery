<?php

/**
 * Provides the "Display Tab" for the Attach To Post interface/controller
 */
class Mixin_Attach_To_Post_Display_Tab extends Mixin
{
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
		return $this->object->render_partial('display_tab_source', array(
			'source_label'		=>	_('Source:'),
			'source_templates'	=>	$this->object->_get_source_templates()
		),TRUE);
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
		return $this->object->render_partial('display_tab_type', array(
			'display_types'	=>	$this->object->_get_display_types()
		), TRUE);
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
	 * Renders the contents of the display settings tab
	 * @return string
	 */
	function _render_display_settings_contents()
	{
		return '';
		$retval = array();

		// Retrieve all display types. I'm currently retrieving all as models,
		// as set_defaults() is NOT called otherwise. If there are validation
		// errors too, we need to display them.
		// TODO: Figure out a better way to get validation errors. Models are
		// too expensive to use with collections.
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		foreach ($mapper->find_all(array(), TRUE) as $display_type) {

			// Get the display type controller
			$display_type_controller = $this->object->get_registry()->get_utility(
				'I_Display_Type_Controller', $display_type->name
			);

			// Determine which classes to use for the form's "class" attribute
			$css_class = $this->object->_get_selected_display_type_name() == $display_type->name ?
				'display_settings_form' : 'display_settings_form hidden';

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
		return $this->object->render_partial('preview_tab', array(
			'exclude_all_label'	=>	_('Exclude from displaying ?'),
		), TRUE);
	}


	/**
	 * Retrieves a list of sources for images/galleries
	 */
	function get_sources()
	{
		return array(
			array('id'	=>	'galleries',	'title'	=>	'Galleries'),
			array('id'	=>	'albums',		'title'	=>	'Albums'),
			array('id'	=>	'image_tags',	'title'	=>	'Image Tags'),
			array('id'	=>	'recent_images','title' =>	'Recent Images'),
			array('id'	=>	'random_images','title'	=>	'Random Images')
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


	/**
	 * Gets a list of display types available
	 */
	function _get_display_types()
	{
		// TODO: This is returning display type models. It doesn't need to, other
		// than the fact that we need the set_defaults() method executed. When
		// we move the operation of setting defaults to the datamapper, then
		// we can return simple entities instead
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		return $mapper->find_all(array(), TRUE);
	}
}