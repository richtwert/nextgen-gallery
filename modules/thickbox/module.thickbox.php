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
    
    
    function initialize()
    {
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Lightbox_Library', 'A_Thickbox_Lightbox_Library');
    }
}

new M_Thickbox();
