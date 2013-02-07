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
            'Override Thumbnail Settings',
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
}
