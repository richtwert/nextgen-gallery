<?php

class Mixin_NextGen_Basic_Templates extends Mixin
{

    function _render_nextgen_basic_templates_template_field($display_type)
    {
        return $this->render_partial('nextgen_basic_templates_settings_template', array(
            'display_type_name' => $display_type->name,
            'template_label' => _('Template:'),
            'template' => $display_type->settings['template'],
        ), True);
    }

}
