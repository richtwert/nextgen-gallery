<?php

class Mixin_Display_Type_Controller_Fields extends Mixin
{


	/**
	 * Renders the "Show return Link" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_return_link_text_field($display_type)
	{
		return $this->render_partial(
			'return_link_text',
			array(
				'display_type_name'			=>	$display_type->name,
				'return_link_text_label'	=>	_('Return link text'),
				'tooltip'					=>	_('The text used for the return
												link when using an alternative view, such as a Slideshow'),
				'return_link_text'			=>	$display_type->settings['return_link_text']
			),
			TRUE
		);
	}


	/**
	 * Renders the "Return link text" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_show_return_link_field($display_type)
	{
		return $this->render_partial(
			'show_return_link',
			array(
				'display_type_name'			=>	$display_type->name,
				'show_return_link_label'	=>	_('Show return link'),
				'tooltip'					=>	_('When viewing as a Slideshow,
												   do you want a return link to
												   display Thumbnails?'),
				'show_return_link'			=>	$display_type->settings['show_return_link']
			),
			TRUE
		);
	}

	/**
	 * Renders the "Show alternative view link" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_alternative_view_field($display_type, $template_overrides=array())
	{
		// Params for template
		$template_params = array(
			'display_type_name'			=>	$display_type->name,
			'show_alt_view_link_label'	=>	_('Alternative view link'),
			'tooltip'					=>	_('Show a link that allows end-users to change how a gallery is displayed'),
			'alternative_view'=>	$display_type->settings['alternative_view']
		);

		// Apply overrides
		$template_params = $this->array_merge_assoc(
			$template_params, $template_overrides,TRUE
		);

		// Render the template
		return $this->render_partial(
			'alternative_view',
			$template_params,
			TRUE
		);
	}

	/**
	 * Renders the "Alternative view link text" settings field
	 * @param type $display_type
	 * @param type $template_overrides
	 * @return type
	 */
	function _render_alt_view_link_text_field($display_type, $template_overrides=array()){
		// Params for template
		$template_params = array(
			'display_type_name'				=>	$display_type->name,
			'alt_view_link_text_label'		=>	_('Alternative view link text'),
			'tooltip'						=>	_('The text of the link used to display the alternative view'),
			'alternativew_view_link_text'	=>	$display_type->settings['alternative_view_link_text']
		);

		// Apply overrides
		$template_params = $this->array_merge_assoc(
			$template_params, $template_overrides,TRUE
		);

		// Render the template
		return $this->render_partial(
			'alt_view_link_text',
			$template_params,
			TRUE
		);
	}
}