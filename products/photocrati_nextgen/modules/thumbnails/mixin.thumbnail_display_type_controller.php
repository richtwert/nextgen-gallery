<?php

/**
 * A mixin that provides the ability to render the thumbnail dimensions
 * field
 */
class Mixin_Thumbnail_Display_Type_Controller extends Mixin
{
	/**
	 * Adds a hook to enqueue the static resources required for thumbnail
	 */
	function initialize()
	{
		$this->object->add_post_hook(
			'enqueue_backend_resources',
			'Enqueue Thumbnail Resources for the Backend',
			get_class($this),
			'_enqueue_resources_for_thumbnails'
		);
	}


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
			'thumbnail_dimensions_label'=>	_('Thumbnail Dimensions:'),
			'thumbnail_dimensions'		=>	$settings->thumbnail_dimensions,
			'selected_dimensions'		=>	$this->object->_get_selected_dimensions($display_type),
			'thumbnail_width_label'		=>	_('Thumbnail Width:'),
			'thumbnail_height_label'	=>	_('Thumbnail Height:'),
			'display_type_name'			=>	$display_type->name,
			'thumbnail_width'			=>	$display_type->settings['thumbnail_width'],
			'thumbnail_height'			=>	$display_type->settings['thumbnail_height']
		), TRUE);
	}

    function _render_thumbnail_misc_field($display_type)
    {
        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        return $this->render_partial('nextgen_basic_thumbnail_misc', array(
            'display_type_name'        => $display_type->name,
            'thumbnail_template_label' => _('Template:')
        ), True);
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

	/**
	 * Enqueues resources needed for thumbnails
	 * @param type $displayed_gallery
	 */
	function _enqueue_resources_for_thumbnails($displayed_gallery)
	{
		wp_enqueue_script(
			'ngg_thumbnail_dimensions',
			PHOTOCRATI_GALLERY_MODULE_URL.'/'.basename(__DIR__).'/js/ngg_thumbnail_dimensions.js',
			array('jquery')
		);
	}
}
