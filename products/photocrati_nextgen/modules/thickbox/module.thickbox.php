<?php

/***
    {
        Module: photocrati-thickbox
    }
***/

class M_Thickbox extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-thickbox',
            'Thickbox',
            'Provides integration with the JQuery-based Thickbox library for lightbox effects',
            '0.1',
            'http://jquery.com/demo/thickbox/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_NextGen_Activator', 'A_Thickbox_Library_Activation');
    }
}

new M_Thickbox();
