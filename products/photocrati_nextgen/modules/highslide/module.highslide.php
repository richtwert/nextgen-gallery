<?php

/***
    {
        Module: photocrati-highslide
    }
***/

class M_Highslide extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-highslide',
            'Highslide',
            'Adds integration with the Highslide lightbox plugin',
            '0.1',
            'http://highslide.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Highslide_Library_Activation');
    }
}

new M_Highslide();
