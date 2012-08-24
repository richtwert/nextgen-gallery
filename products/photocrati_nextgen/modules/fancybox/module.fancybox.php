<?php

/***
    {
        Module: photocrati-fancybox-1x
    }
***/

define('PHOTOCRATI_GALLERY_FANCYBOX_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__))) . '/static/fancybox/jquery.fancybox-1.3.4.pack.js'
);

define('PHOTOCRATI_GALLERY_FANCYBOXY_JS_INIT_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__))) . '/static/fancybox/nextgen_fancybox_init.js'
);

define('PHOTOCRATI_GALLERY_JQUERY_EASING_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__))) . '/static/fancybox/jquery.easing-1.3.pack.js'
);

define('PHOTOCRATI_GALLERY_JQUERY_FANCYBOX_CSS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)) . '/static/fancybox/jquery.fancybox-1.3.4.css'
));

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
