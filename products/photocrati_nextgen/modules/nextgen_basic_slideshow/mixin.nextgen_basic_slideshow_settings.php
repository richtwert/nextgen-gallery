<?php

class Mixin_NextGen_Basic_Slideshow_Settings extends Mixin
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
            'nextgen_basic_slideshow_gallery_dimensions',
            'nextgen_basic_slideshow_cycle_effect',
            'nextgen_basic_slideshow_cycle_interval',
            'nextgen_basic_slideshow_flash_enabled',
            'nextgen_basic_slideshow_flash_background_music',
            'nextgen_basic_slideshow_flash_stretch_image',
            'nextgen_basic_slideshow_flash_transition_effect',
            'nextgen_basic_slideshow_flash_shuffle',
            'nextgen_basic_slideshow_flash_next_on_click',
            'nextgen_basic_slideshow_flash_navigation_bar',
            'nextgen_basic_slideshow_flash_loading_icon',
            'nextgen_basic_slideshow_flash_watermark_logo',
            'nextgen_basic_slideshow_flash_slow_zoom',
            'nextgen_basic_slideshow_flash_xhtml_validation',
            'nextgen_basic_slideshow_flash_background_color',
            'nextgen_basic_slideshow_flash_text_color',
            'nextgen_basic_slideshow_flash_rollover_color',
            'nextgen_basic_slideshow_flash_screen_color',
            'show_alternative_view_link',
            'alternative_view',
            'alternative_view_link_text',
            'show_return_link',
            'return_link_text',
            'nextgen_basic_templates_template',
        );
    }

    function _render_nextgen_basic_slideshow_cycle_interval_field($display_type)
    {
        return $this->_render_number_field(
            $display_type,
            'cycle_interval',
            'Interval',
            $display_type->settings['cycle_interval'],
            '',
            FALSE,
            '# of seconds',
            1
        );
    }

    function _render_nextgen_basic_slideshow_cycle_effect_field($display_type)
    {
        return $this->_render_select_field(
            $display_type,
            'cycle_effect',
            'Effect',
            $display_type->settings['cycle_effect'],
            '',
            FALSE,
            array(
                'fade',
                'blindX',
                'cover',
                'scrollUp',
                'scrollDown',
                'shuffle',
                'toss',
                'wipe'
            )
        );
    }

    function _render_nextgen_basic_slideshow_gallery_dimensions_field($display_type)
    {
        return $this->render_partial('nextgen_basic_slideshow_settings_gallery_dimensions', array(
            'display_type_name' => $display_type->name,
            'gallery_dimensions_label' => _('Gallery dimensions'),
            'gallery_width' => $display_type->settings['gallery_width'],
            'gallery_height' => $display_type->settings['gallery_height'],
        ), True);
    }

    function _render_nextgen_basic_slideshow_flash_enabled_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_enabled',
            'Enable flash slideshow',
            $display_type->settings['flash_enabled'],
            'Integrate the flash based slideshow for all flash supported devices'
        );
    }

    function _render_nextgen_basic_slideshow_flash_shuffle_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_shuffle',
            'Shuffle',
            $display_type->settings['flash_shuffle'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_next_on_click_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_next_on_click',
            'Show next image on click',
            $display_type->settings['flash_next_on_click'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_navigation_bar_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_navigation_bar',
            'Show navigation bar',
            $display_type->settings['flash_navigation_bar'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_loading_icon_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_loading_icon',
            'Show loading icon',
            $display_type->settings['flash_loading_icon'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_watermark_logo_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_watermark_logo',
            'Use watermark logo',
            $display_type->settings['flash_watermark_logo'],
            'Use the watermark image in the Flash object. Note: this does not watermark the image itself, and cannot be applied with text watermarks',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_stretch_image_field($display_type)
    {
        return $this->_render_select_field(
            $display_type,
            'flash_stretch_image',
            'Stretch image',
            $display_type->settings['flash_stretch_image'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE,
            array('true', 'false', 'fit', 'none')
        );
    }

    function _render_nextgen_basic_slideshow_flash_transition_effect_field($display_type)
    {
        return $this->_render_select_field(
            $display_type,
            'flash_transition_effect',
            'Transition / fade effect',
            $display_type->settings['flash_transition_effect'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE,
            array(
                'fade',
                'bgfade',
                'slowfade',
                'circles',
                'bubbles',
                'blocks',
                'fluids',
                'flash',
                'lines',
                'random'
            )
        );
    }

    function _render_nextgen_basic_slideshow_flash_slow_zoom_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_slow_zoom',
            'Use slow zooming effect',
            $display_type->settings['flash_slow_zoom'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_background_music_field($display_type)
    {
        return $this->_render_text_field(
            $display_type,
            'flash_background_music',
            'Background music (url)',
            $display_type->settings['flash_background_music'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE,
            'http://...'
        );
    }

    function _render_nextgen_basic_slideshow_flash_xhtml_validation_field($display_type)
    {
        return $this->_render_radio_field(
            $display_type,
            'flash_xhtml_validation',
            'Try XHTML validation',
            $display_type->settings['flash_xhtml_validation'],
            'Uses CDATA. Important: Could cause problems with some older browsers',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_background_color_field($display_type)
    {
        return $this->_render_color_field(
            $display_type,
            'flash_background_color',
            'Background',
            $display_type->settings['flash_background_color'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_text_color_field($display_type)
    {
        return $this->_render_color_field(
            $display_type,
            'flash_text_color',
            'Texts / buttons',
            $display_type->settings['flash_text_color'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_rollover_color_field($display_type)
    {
        return $this->_render_color_field(
            $display_type,
            'flash_rollover_color',
            'Rollover / active',
            $display_type->settings['flash_rollover_color'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

    function _render_nextgen_basic_slideshow_flash_screen_color_field($display_type)
    {
        return $this->_render_color_field(
            $display_type,
            'flash_screen_color',
            'Screen',
            $display_type->settings['flash_screen_color'],
            '',
            empty($display_type->settings['flash_enabled']) ? TRUE : FALSE
        );
    }

}