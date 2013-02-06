<?php

class Mixin_NextGen_Basic_Thumbnails_Settings extends Mixin
{

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array(
            'nextgen_basic_thumbnails_thumbnail_settings',
            'nextgen_basic_thumbnails_images_per_page',
            'nextgen_basic_thumbnails_number_of_columns',
            'nextgen_basic_thumbnails_piclens_link_text',
            'nextgen_basic_thumbnails_show_piclens_link',
            'nextgen_basic_thumbnails_ajax_pagination',
            'nextgen_basic_thumbnails_hidden',
            'show_alternative_view_link',
            'show_return_link',
            'alternative_view',
            'alternative_view_link_text',
            'return_link_text',
            'nextgen_basic_templates_template',
        );
    }

    /**
     * Renders the thumbnail generation settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_thumbnail_settings_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_thumbnail_settings',
            array(
                'display_type_name' => $display_type->name,
                'override_thumbnail_settings_label' => _('Override Thumbnail Settings'),
                'override_thumbnail_settings' => $display_type->settings['override_thumbnail_settings'],
                'thumbnail_dimensions_label'=>	_('Thumbnail dimensions'),
                'thumbnail_width'		=>	$display_type->settings['thumbnail_width'],
                'thumbnail_height'		=>	$display_type->settings['thumbnail_height'],
                'thumbnail_quality_label'=>	_('Thumbnail Quality'),
                'thumbnail_quality'=>	$display_type->settings['thumbnail_quality'],
                'thumbnail_crop_label'=>	_('Thumbnail Crop'),
                'thumbnail_crop'=>	$display_type->settings['thumbnail_crop'],
                'thumbnail_watermark_label'=>	_('Thumbnail Watermark'),
                'thumbnail_watermark'=>	$display_type->settings['thumbnail_watermark'],
            ),
            TRUE
        );
    }

    /**
     * Renders the images_per_page settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_images_per_page_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_images_per_page',
            array(
                'display_type_name' => $display_type->name,
                'images_per_page_label' => _('Images per page'),
                'images_per_page' => $display_type->settings['images_per_page'],
            ),
            TRUE
        );
    }

    /**
     * Renders the number_of_columns settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_number_of_columns_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_number_of_columns',
            array(
                'display_type_name' => $display_type->name,
                'number_of_columns_label' => _('Number of columns to display'),
                'number_of_columns' => $display_type->settings['number_of_columns']
            ),
            TRUE
        );
    }

    /**
     * Renders the piclens_link_text settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_piclens_link_text_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_piclens_link_text',
            array(
                'display_type_name' => $display_type->name,
                'piclens_link_text_label' => _('Piclens link text'),
                'piclens_link_text' => $display_type->settings['piclens_link_text']
            ),
            TRUE
        );
    }

    /**
     * Renders the show_piclens_link settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_show_piclens_link_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_show_piclens_link',
            array(
                'display_type_name' => $display_type->name,
                'show_piclens_link_label' => _('Show piclens link'),
                'show_piclens_link' => $display_type->settings['show_piclens_link']
            ),
            TRUE
        );
    }

    /**
     * Renders the show_piclens_link settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_hidden_field($display_type)
    {
        return $this->render_partial(
            'nextgen_basic_thumbnails_settings_hidden',
            array(
                'display_type_name' => $display_type->name,
                'show_all_in_lightbox_label' => _('Add Hidden Images'),
                'show_all_in_lightbox_desc' => _('If pagination is used this option will show all images in the modal window (Thickbox, Lightbox etc.) This increases page load.'),
                'show_all_in_lightbox' => $display_type->settings['show_all_in_lightbox']
            ),
            TRUE
        );
    }

    /**
     * Renders the show_piclens_link settings field
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_nextgen_basic_thumbnails_ajax_pagination_field($display_type)
    {
        return $this->render_partial('nextgen_basic_thumbnails_settings_ajax_pagination', array(
            'display_type_name' => $display_type->name,
            'ajax_pagination_label' => _('Enable Ajax pagination'),
            'ajax_pagination_desc' => _('Browse images without reloading the page.'),
            'ajax_pagination' => $display_type->settings['ajax_pagination']
        ), TRUE);
    }
}