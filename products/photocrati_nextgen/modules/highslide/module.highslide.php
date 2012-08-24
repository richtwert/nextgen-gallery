<?php

/***
    {
        Module: photocrati-highslide
    }
 ***/

define('PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)) . '/static/highslide/highslide-full.packed.js'
));

define('PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_JS_INIT_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__)) . '/static/highslide/nextgen_highslide_init.js'
));

define('PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_CSS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)) . '/static/highslide/highslide.css'
));

define('PHOTOCRATI_GALLERY_MOD_HIGHSLIDE_GRAPHICS_DIR', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)) . '/static/highslide/graphics/'
));

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
