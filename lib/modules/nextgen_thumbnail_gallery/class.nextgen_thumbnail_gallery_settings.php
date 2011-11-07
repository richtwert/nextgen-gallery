<?php

class C_NextGen_Thumbnail_Gallery_Settings extends C_Thumbnail_Settings
{
    function preview()
    {
        $src = $this->static_url('preview.jpg');
        $this->render_partial('preview', array('src' => $src));
    }
    
    
    function settings_js()
    {
        $this->set_content_type('application/x-javascript');
        $this->render_view('settings_js');
    }
    
    function custom_display_template()
    {
        $template_dirs = array(
            path_join(STYLESHEETPATH, "nggallery"),
            path_join(NGGALLERY_ABSPATH, 'view')
        );
        
        $this->render_view('custom_display_template', array(
            'template_dirs' => $template_dirs
        ));
    }
}