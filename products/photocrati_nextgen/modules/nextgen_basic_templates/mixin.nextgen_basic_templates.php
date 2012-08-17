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

    function legacy_render($template_name, $vars = array(), $callback = false)
    {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        // hook into the render feature to allow other plugins to include templates
        $custom_template = apply_filters('ngg_render_template', false, $template_name);

        if (($custom_template != false) && file_exists($custom_template))
        {
            include($custom_template);
        }
        else if (file_exists(STYLESHEETPATH . "/nggallery/{$template_name}.php"))
        {
            include (STYLESHEETPATH . "/nggallery/{$template_name}.php");
        }
        else if (file_exists (NGGALLERY_ABSPATH . "/view/{$template_name}.php"))
        {
            include (NGGALLERY_ABSPATH . "/view/{$template_name}.php");
        }
        else if ($callback === true)
        {
            echo "<p>Rendering of template {$template_name}.php failed</p>";
        }
        else {
            // test without the "-template" name one time more
            $template_name = array_shift(explode('-', $template_name , 2));
            $this->render($template_name, $vars , true);
        }
    }

}
