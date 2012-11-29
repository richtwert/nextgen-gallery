<?php

/***
    {
        Module: photocrati-shutter
    }
 ***/

class M_Shutter extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-shutter',
            'Shutter',
            'Provides integration with the Shutter lightbox plugin',
            '0.1',
            'http://www.laptoptips.ca/javascripts/shutter-reloaded/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Shutter_Library_Activation');
    }
}

new M_Shutter();
