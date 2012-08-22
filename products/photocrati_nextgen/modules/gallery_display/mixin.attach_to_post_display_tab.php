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


	function _render_display_types_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'display_type_tab',
			'title'		=>	_('Select a display type'),
			'content'	=>	$this->object->_render_display_type_tab_contents()
		), TRUE);
	}


	function _render_display_type_tab_contents()
	{

	}


	function _render_display_settings_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'display_settings_tab',
			'title'		=>	_('Customize the display settings'),
			'content'	=>	$this->object->_render_display_settings_contents()
		), TRUE);
	}


	function _render_display_settings_contents()
	{
		return 'here';
	}


	/**
	 * Renders the tab used to preview included images
	 * @return string
	 */
	function _render_preview_tab()
	{
		return $this->object->render_partial('accordion_tab', array(
			'id'			=> 'preview_tab',
			'title'		=>	_('Select individual images to display'),
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
			array('id'	=>	'tags',			'title'	=>	'Image Tags'),
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
}