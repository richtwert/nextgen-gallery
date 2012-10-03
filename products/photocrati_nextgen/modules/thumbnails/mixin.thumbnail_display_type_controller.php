<?php

/**
 * A mixin that provides the ability to render the thumbnail dimensions
 * field
 */
class Mixin_Thumbnail_Display_Type_Controller extends Mixin
{
	/**
	 * Renders the field to select thumbnail dimensions
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_thumbnail_dimensions_field($display_type)
	{
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

		return $this->render_partial('nextgen_basic_thumbnail_dimensions', array(
			'hidden_customization_label'=> _('Show Customization Options'),
			'active_customization_label'=> _('Hide Customization Options'),
			'thumbnail_dimensions_label'=>	_('Thumbnail dimensions'),
			'thumbnail_dimensions'		=>	$settings->thumbnail_dimensions,
			'selected_dimensions'		=>	$this->object->_get_selected_dimensions($display_type),
			'thumbnail_width_label'		=>	_('Thumbnail width'),
			'thumbnail_height_label'	=>	_('Thumbnail height'),
			'display_type_name'			=>	$display_type->name,
			'thumbnail_width'			=>	$display_type->settings['thumbnail_width'],
			'thumbnail_height'			=>	$display_type->settings['thumbnail_height']
		), TRUE);
	}

	/**
	 * Returns the selected thumbnail dimension for the display type
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _get_selected_dimensions($display_type)
	{
		return "{$display_type->settings['thumbnail_width']}x{$display_type->settings['thumbnail_height']}";
	}
}
