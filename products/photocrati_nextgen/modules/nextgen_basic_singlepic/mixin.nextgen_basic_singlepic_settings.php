<?php

class Mixin_NextGen_Basic_Singlepic_Settings extends Mixin
{

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array(
            'nextgen_basic_singlepic_dimensions',
            'nextgen_basic_singlepic_link',
            'nextgen_basic_singlepic_mode',
            'nextgen_basic_singlepic_float',
            'nextgen_basic_templates_template'
        );
    }

    function _render_nextgen_basic_singlepic_dimensions_field($display_type)
    {
        return $this->object->render_partial(
            'nextgen_basic_singlepic_settings_dimensions',
            array(
                'display_type_name' => $display_type->name,
                'dimensions_label' => _('Thumbnail dimensions'),
                'width_label' => _('Width'),
                'width' => $display_type->settings['width'],
                'height_label' => _('Width'),
                'height' => $display_type->settings['height'],
            ),
            True
        );
    }

    function _render_nextgen_basic_singlepic_link_field($display_type)
    {
        return $this->object->render_partial(
            'nextgen_basic_singlepic_settings_link',
            array(
                'display_type_name' => $display_type->name,
                'link_label' => _('Link'),
                'link' => $display_type->settings['link'],
            ),
            True
        );
    }

    function _render_nextgen_basic_singlepic_mode_field($display_type)
    {
        return $this->_render_select_field(
            $display_type,
            'mode',
            'Mode',
            array('' => 'None', 'watermark' => 'Watermark', 'web20' => 'Web 2.0'),
            $display_type->settings['mode']
        );
    }

    function _render_nextgen_basic_singlepic_float_field($display_type)
    {
        return $this->_render_select_field(
            $display_type,
            'float',
            'Float',
            array('' => 'None', 'left' => 'Left', 'right' => 'Right'),
            $display_type->settings['float']
        );
    }

    function _render_select_field($display_type, $name, $label, $options, $value)
    {
        return $this->object->render_partial(
            'nextgen_basic_singlepic_settings_select',
            array(
                'display_type_name' => $display_type->name,
                'name'    => $name,
                'label'   => _($label),
                'options' => $options,
                'value'   => $value
            ),
            True
        );
    }
}
