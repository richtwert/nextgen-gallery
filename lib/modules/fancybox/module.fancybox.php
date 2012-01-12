<?php

/***
    {
        Module: photocrati-fancybox-1x
    }
***/

define('PHOTOCRATI_GALLERY_FANCYBOX_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL, 
        basename(dirname(__FILE__))).'/static/fancybox/jquery.fancybox-1.3.4.pack.js'
);

define('PHOTOCRATI_GALLERY_JQUERY_EASING_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL, 
        basename(dirname(__FILE__))).'/static/fancybox/jquery.easing-1.3.pack.js'
);

define('PHOTOCRATI_GALLERY_JQUERY_FANCYBOX_CSS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/fancybox/jquery.fancybox-1.3.4.css'
));

class M_Fancybox extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
            'photocrati-fancybox-1x',
            'FancyBox 1.x',
            'Provides integration with the FancyBox JQuery lightbox library (1.x series)',
            '0.1',
            'http://www.fancybox.net',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    function _register_hooks()
    {
        wp_register_script(
            'fancybox',
            PHOTOCRATI_GALLERY_FANCYBOX_JS_URL,
            array('jquery.easing'), 
            '1.3.4'
        );
        
        wp_register_script(
            'jquery.easing',
            PHOTOCRATI_GALLERY_JQUERY_EASING_JS_URL,
            array('jquery'),
            '1.3'
        );
        
        wp_register_style(
            'fancybox',
            PHOTOCRATI_GALLERY_JQUERY_FANCYBOX_CSS_URL,
            array(),
            '1.3.4'
        );
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Lightbox_Library', 'A_Fancybox_Lightbox_Library');
    }
}

new M_Fancybox();