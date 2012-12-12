<?php

/***
    {
        Module: photocrati-fancybox-1x
    }
***/

define('NEXTGEN_GALLERY_FANCYBOX_VERSION', '1.3.4');

class M_Fancybox extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-fancybox-1x',
            'FancyBox 1.x',
            'Provides integration with the FancyBox JQuery lightbox library (1.x series)',
            '0.1',
            'http://www.fancybox.net',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Fancybox_Library_Activation');
    }
}

new M_Fancybox();
