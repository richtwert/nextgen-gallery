<?php

class C_NextGen_Slideshow_Gallery_Settings extends C_Base_Gallery_Settings_Controller
{
    function configure_fields()
    {
        $this->append_field(array(
            'id'        =>  'ngg_slideshow_size',
            'name'      => 'slideshow_size',
            'label'     =>  _('Slideshow Size'),
            'template'  =>  'slideshow_size'
        ));
        
        $this->append_field(array(
            'id'        =>  'ngg_slideshow_duration',
            'name'      =>  'slideshow_duration',
            'label'     => _('Slideshow Duration'),
            'template'  =>  'slideshow_duration'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_transition_effect',
            'name'      => 'slideshow_transition_effect',
            'label'     => _('Slideshow Transition Effect'),
            'template'  =>  'slideshow_transition_effect'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_enable_flash',
            'name'      => 'slideshow_enable_flash',
            'label'     => _('Enable Flash Slideshow?'),
            'template'  => 'slideshow_enable_flash',
            'help'      => _('Enable support for JW Image Rotator, a flash plugin.')
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_jw_image_rotator_path',
            'name'      => 'slideshow_jw_image_rotator_path',
            'label'     => _('JW Image Rotator Path'),
            'template'  => 'slideshow_jw_image_rotator_path',
            'help'      => 'Absolute path to the JW Image Rotator plugin'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_shuffle',
            'label'     => _('Enable Shuffle Mode?'),
            'name'      => 'slideshow_shuffle',
            'template'  => 'slideshow_shuffle',
            'help'      => 'Shuffle the slides in the slideshow'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_show_next_image_on_click',
            'label'     => _('Show Next Image on Click?'),
            'name'      => 'slideshow_show_next_image_on_click',
            'template'  => 'slideshow_show_next_image_on_click'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_show_navigation_bar',
            'label'     => _('Show Navigation Bar?'),
            'name'      => 'slideshow_show_navigation_bar',
            'template'  => 'slideshow_show_navigation_bar'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_show_loading_icon',
            'label'     => _('Show Loading Icon?'),
            'name'      => 'slideshow_show_loading_icon',
            'template'  => 'slideshow_show_loading_icon',
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_stretch_image',
            'name'      => 'slideshow_stretch_image',
            'label'     => _('Stretch Image to Fit?'),
            'template'  => 'slideshow_stretch_image',
            'help'      => _('Stretches slideshow images to fit the slideshow dimensions')
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_background_color',
            'name'      => 'slideshow_background_color',
            'label'     => _('Background Color'),
            'template'  => 'slideshow_background_color',
        ));
        
        $this->append_field(array(
            'name'      => 'slideshow_button_color',
            'id'        => 'ngg_slideshow_button_color',
            'label'     => _('Button Color'),
            'template'  => 'slideshow_button_color'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_rollover_color',
            'name'      => 'slideshow_rollover_color',
            'label'     => _('Rollover / Active Color'),
            'template'  => 'slideshow_rollover_color',
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_screen_color',
            'name'      => 'slideshow_screen_color',
            'label'     => _('Screen Color'),
            'template'  =>  'slideshow_screen_color'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_background_music',
            'name'      => 'slideshow_background_music',
            'label'     => _('Background Music URL'),
            'template'  => 'slideshow_background_music'
        ));
        
        $this->append_field(array(
            'id'        => 'ngg_slideshow_valid_xhtml',
            'name'      => 'slideshow_valid_xhtml',
            'label'     => _('Try XHTML Validation?'),
            'template'  => 'slideshow_valid_xhtml',
            'help'      => 'Uses CDATA to force XHTML validation. Could causes problem at some browser. Please recheck your page.'
        ));
    }
}