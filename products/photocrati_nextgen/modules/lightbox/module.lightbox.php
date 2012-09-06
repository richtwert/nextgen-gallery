<?php

/***
    {
        Module: photocrati-lightbox
    }
***/

class M_Lightbox extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-lightbox',
            'Lightbox',
            _("Provides integration with JQuery's lightbox plugin"),
            '0.1',
            'http://leandrovieira.com/projects/jquery/lightbox/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
	
    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_NextGen_Activator', 'A_JQuery_Lightbox_Library_Activation');
    }
}

new M_Lightbox();
