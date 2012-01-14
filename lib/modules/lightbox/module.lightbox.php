<?php

/***
    {
        Module: photocrati-jquery-lightbox
    }
***/

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_JS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/js/jquery.lightbox-0.5.pack.js'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_CSS_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/css/jquery.lightbox-0.5.css'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_LOADING_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/images/lightbox-ico-loading.gif'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BLANK_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/images/lightbox-blank.gif'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_CLOSE_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/images/lightbox-btn-close.gif'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_NEXT_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/images/lightbox-btn-next.gif'
));

define('PHOTOCRATI_GALLERY_MOD_LIGHTBOX_IMG_BTN_PREV_URL', path_join(
        PHOTOCRATI_GALLERY_MODULE_URL,
        basename(dirname(__FILE__)).'/static/images/lightbox-btn-prev.gif'
));


class M_Lightbox extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
            'photocrati-lightbox',
            'Lightbox',
            _("Provides integration with JQuery's lightbox plugin"),
            '0.1',
            'http://leandrovieira.com/projects/jquery/lightbox/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    function _register_hooks()
    {
        wp_register_script(
            'jquery.lightbox',
            PHOTOCRATI_GALLERY_MOD_LIGHTBOX_JS_URL,
            array('jquery'),
            '0.5'
        );
        
        wp_register_style(
            'jquery.lightbox',
            PHOTOCRATI_GALLERY_MOD_LIGHTBOX_CSS_URL,
            array(),
            '0.5'
        );
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter(
            'I_Lightbox_Library',
            'A_JQuery_Lightbox_Library'
        );
    }
}

new M_Lightbox();