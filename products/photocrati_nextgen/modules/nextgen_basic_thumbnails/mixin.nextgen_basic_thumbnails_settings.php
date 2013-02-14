<?php

class Mixin_NextGen_Basic_Thumbnails_Settings extends Mixin
{
    function initialize()
    {
        $this->add_mixin('Mixin_NextGen_Settings_Form_Field_Generators');
    }

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array(
            'thumbnail_override_settings',
            'nextgen_basic_thumbnails_images_per_page',
            'nextgen_basic_thumbnails_number_of_columns',
            'nextgen_basic_thumbnails_ajax_pagination',
            'nextgen_basic_thumbnails_hidden',
            'nextgen_basic_thumbnails_show_piclens_link',
            'nextgen_basic_thumbnails_piclens_link_text',
            'show_alternative_view_link',
            'alternative_view',
            'alternative_view_link_text',
            'show_return_link',
            'return_link_text',
            'nextgen_basic_templates_template',
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
        return $this->_render_number_field(
            $display_type,
            'images_per_page',
            'Images per page',
            $display_type->settings['images_per_page'],
            '',
            FALSE,
            '# of images',
            1
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
        return $this->_render_number_field(
            $display_type,
            'number_of_columns',
            'Number of columns to display',
            $display_type->settings['number_of_columns'],
            '',
            FALSE,
            '# of columns',
            0
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
        return $this->_render_text_field(
            $display_type,
            'piclens_link_text',
            'Piclens link text',
            $display_type->settings['piclens_link_text'],
            '',
            !empty($display_type->settings['show_piclens_link']) ? FALSE : TRUE
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
        return $this->_render_radio_field(
            $display_type,
            'show_piclens_link',
            'Show piclens link',
            $display_type->settings['show_piclens_link']
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
        return $this->_render_radio_field(
            $display_type,
            'show_all_in_lightbox',
            'Add Hidden Images',
            $display_type->settings['show_all_in_lightbox'],
            'If pagination is used this option will show all images in the modal window (Thickbox, Lightbox etc.) This increases page load.'
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
        return $this->_render_radio_field(
            $display_type,
            'ajax_pagination',
            'Enable Ajax pagination',
            $display_type->settings['ajax_pagination'],
            'Browse images without reloading the page.'
        );
    }
}