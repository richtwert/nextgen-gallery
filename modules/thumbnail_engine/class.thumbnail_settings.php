<?php

class C_Thumbnail_Settings extends C_Base_Gallery_Settings_Controller
{      
    var $form_identifier = __CLASS__;
    
    function configure_fields()
    {   
        $this->append_field(array(
            'id'        => 'thumbnail_width',
            'name'      => 'thumbnail_width',
            'label'     => _("Thumbnail Size"),
            'template'  => 'thumbnail_size'
        ));
        
        $this->append_field(array(
            'id'        => 'thumbnail_crop',
            'name'      => 'thumbnail_crop',
            'label'     => _('Crop Thumbnails?'),
            'template'  => 'thumbnail_crop',
            'help'      => _("With thumbnail cropping ON all thumbnails will
                            be sized at the exact height and width specified
                            and will be automatically cropped. Turning
                            cropping OFF will display thumbnails at their 
                            original aspect ratio BUT will result in uneven
                            gallery layouts if mixed sized images are used.")
        ));
        
        $this->append_field(array(
            'id'     => 'thumbnail_quality',
            'name'     => 'thumbnail_quality',
            'label'  => _("Thumbnail Quality"),
            'template'   => 'thumbnail_quality'
        ));
        
        $this->append_field(array(
            'id'        => 'generate_thumbnails',
            'name'      => 'generate_thumbnails',
            'template'  => 'generate_thumbnails',
            'hidden'    => TRUE
        ));
    }
    
    function get_config()
    {
        $factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
        return $this->config = $factory->create(
            'thumbnail_config',
            $this->handle_this_form()? $this->param('settings') : array()
        );
    }
}