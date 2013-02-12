<?php

class Mixin_NextGen_Settings_Form_Field_Generators extends Mixin
{
    function _render_select_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $options)
    {
        return $this->object->render_partial(
            'nextgen_settings_field_select',
            array(
                'display_type_name' => $display_type->name,
                'name'    => $name,
                'label'   => _($label),
                'options' => $options,
                'value'   => $value,
                'text'    => $text,
                'hidden'  => $hidden
            ),
            True
        );
    }

    function _render_radio_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial(
            'nextgen_settings_field_radio',
            array(
                'display_type_name' => $display_type->name,
                'name'   => $name,
                'label'  => _($label),
                'value'  => $value,
                'text'   => $text,
                'hidden' => $hidden
            ),
            True
        );
    }

    function _render_number_field($display_type,
                                  $name,
                                  $label,
                                  $value,
                                  $text = '',
                                  $hidden = FALSE,
                                  $placeholder = '',
                                  $min = NULL,
                                  $max = NULL)
    {
        return $this->object->render_partial(
            'nextgen_settings_field_number',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden,
                'placeholder' => $placeholder,
                'min' => $min,
                'max' => $max
            ),
            True
        );
    }

    function _render_text_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $placeholder = '')
    {
        return $this->object->render_partial(
            'nextgen_settings_field_text',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden,
                'placeholder' => $placeholder
            ),
            True
        );
    }

    function _render_color_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial(
            'nextgen_settings_field_color',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden
            ),
            True
        );
    }

    /**
     * Renders the thumbnail override settings field(s)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_thumbnail_override_settings_field($display_type)
    {
        $override_field = $this->_render_radio_field(
            $display_type,
            'override_thumbnail_settings',
            'Override thumbnail settings',
            $display_type->settings['override_thumbnail_settings']
        );

        $dimensions_field = $this->render_partial(
            'nextgen_basic_thumbnails_settings_thumbnail_settings',
            array(
                'display_type_name' => $display_type->name,
                'name' => 'thumbnail_dimensions',
                'label'=> _('Thumbnail dimensions'),
                'thumbnail_width' => $display_type->settings['thumbnail_width'],
                'thumbnail_height'=> $display_type->settings['thumbnail_height'],
                'hidden' => empty($display_type->settings['override_thumbnail_settings']) ? 'hidden' : '',
                'text' => ''
            ),
            TRUE
        );

        $qualities = array();
        for ($i = 100; $i > 50; $i--) { $qualities[$i] = "{$i}%"; }
        $quality_field = $this->_render_select_field(
            $display_type,
            'thumbnail_quality',
            'Thumbnail quality',
            $display_type->settings['thumbnail_quality'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE,
            $qualities
        );

        $crop_field = $this->_render_radio_field(
            $display_type,
            'thumbnail_crop',
            'Thumbnail crop',
            $display_type->settings['thumbnail_crop'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE
        );

        $watermark_field = $this->_render_radio_field(
            $display_type,
            'thumbnail_watermark',
            'Thumbnail watermark',
            $display_type->settings['thumbnail_watermark'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE
        );

        $everything = $override_field . $dimensions_field . $quality_field . $crop_field . $watermark_field;

        return $everything;
    }

    /**
     * Renders the thumbnail override settings field(s)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_image_override_settings_field($display_type)
    {
        $override_field = $this->_render_radio_field(
            $display_type,
            'override_image_settings',
            'Override image settings',
            $display_type->settings['override_image_settings']
        );

        $qualities = array();
        for ($i = 100; $i > 50; $i--) { $qualities[$i] = "{$i}%"; }
        $quality_field = $this->_render_select_field(
            $display_type,
            'image_quality',
            'Image quality',
            $display_type->settings['image_quality'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE,
            $qualities
        );

        $crop_field = $this->_render_radio_field(
            $display_type,
            'image_crop',
            'Image crop',
            $display_type->settings['image_crop'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE
        );

        $watermark_field = $this->_render_radio_field(
            $display_type,
            'image_watermark',
            'Image watermark',
            $display_type->settings['image_watermark'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE
        );

        $everything = $override_field . $quality_field . $crop_field . $watermark_field;

        return $everything;
    }

    /**
     * Renders a pair of fields for width and width-units (px, em, etc)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_width_and_unit_field($display_type)
    {
        return $this->object->render_partial(
            'nextgen_settings_field_width_and_unit',
            array(
                'display_type_name' => $display_type->name,
                'name' => 'width',
                'label' => 'Gallery width',
                'value' => $display_type->settings['width'],
                'text' => 'An empty or "0" setting will make the gallery full width',
                'placeholder' => '(optional)',
                'unit_name' => 'width_unit',
                'unit_value' => $display_type->settings['width_unit'],
                'options' => array('px' => 'Pixels', '%' => 'Percent')
            ),
            TRUE
        );
    }

    function _get_aspect_ratio_options()
    {
        return array(
            '0'     => 'Device/Browser default',
            '1.5'   => '3:2 [1.5]',
            '1.333' => '4:3 [1.333]',
            '1.777' => '16:9 [1.777]',
            '1.6'   => '16:10 [1.6]',
            '1.85'  => '1.85:1 [1.85]',
            '2.39'  => '2.39:1 [2.39]',
            '1.81'  => '1.81:1 [1.81]',
            '1'     => '1:1 (Square) [1]'
        );
    }
}
