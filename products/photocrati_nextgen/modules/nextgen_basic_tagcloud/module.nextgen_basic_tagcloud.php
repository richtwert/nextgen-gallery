<?php

/***
{
        Module:     photocrati-nextgen_basic_tagcloud,
        Depends:    { photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

class M_NextGen_Basic_Tagcloud extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_basic_tagcloud',
            'NextGen Basic Tagcloud',
            'Provides a tagcloud for NextGEN Gallery',
            '1.9.6',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }


    function _register_adapters()
    {
        // Installs the display type
        $this->get_registry()->add_adapter(
            'I_NextGen_Activator',
            'A_NextGen_Basic_Tagcloud_Activation'
        );

        // Provides settings fields and frontend rendering
        $this->get_registry()->add_adapter(
            'I_Display_Type_Controller',
            'A_NextGen_Basic_Tagcloud_Controller',
            $this->module_id
        );

        // Provides validation for the display type
        $this->get_registry()->add_adapter(
            'I_Display_Type',
            'A_NextGen_Basic_Tagcloud'
        );
    }
}

new M_NextGen_Basic_Tagcloud();
