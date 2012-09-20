<?php

class Mixin_NextGen_Basic_Tagcloud_Settings extends Mixin
{
    function _get_field_names()
    {
        return array(
            'nextgen_basic_tagcloud_display_type'
        );
    }

    function _render_nextgen_basic_tagcloud_display_type_field($display_type)
    {
        $types = array();
        $skip_types = array(
            'photocrati-nextgen_basic_tagcloud',
            'photocrati-nextgen_basic_singlepic'
        );
        $mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
        $display_types = $mapper->find_all();
        foreach ($display_types as $dt) {
            if (in_array($dt->name, $skip_types)) continue;
            $types[$dt->name] = str_replace('NextGEN Basic ', '', $dt->title);
        }

        return $this->_render_select_field(
            $display_type,
            'display_type',
            'Display type',
            $types,
            $display_type->settings['display_type'],
            'The display type that the tagcloud will point its results to'
        );
    }

    function _render_select_field($display_type, $name, $label, $options, $value, $text = NULL)
    {
        return $this->object->render_partial(
            'nextgen_basic_tagcloud_settings_select',
            array(
                'display_type_name' => $display_type->name,
                'name'    => $name,
                'label'   => _($label),
                'options' => $options,
                'value'   => $value,
                'text'    => $text
            ),
            True
        );
    }
}
