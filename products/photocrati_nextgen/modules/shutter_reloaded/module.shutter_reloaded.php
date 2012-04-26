<?php

/***
    {
        Module: photocrati-shutter-reloaded
    }
 ***/

define('PHOTOCRATI_GALLERY_SHUTTER_RELOADED_CSS_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__)).'/static/shutter/shutter.css'
));

define('PHOTOCRATI_GALLERY_SHUTTER_RELOADED_JS_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__)).'/static/shutter/shutter.js'
));

define('PHOTOCRATI_GALLERY_SHUTTER_IMAGES_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__)).'/static/shutter/images/'
));

class M_Shutter_Reloaded extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-shutter-reloaded',
            'Shutter Reloaded',
            'Provides integration with the Shutter Reloaded lightbox plugin',
            '0.1',
            'http://www.laptoptips.ca/javascripts/shutter-reloaded/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function initialize()
    {
    }
    
    function _register_hooks()
    {
        add_action('wp_enqueue_scripts', array($this, '_enqueue_scripts'));
    }
    
    
    function _register_adapters()
    {
        $this->_get_registry()->add_adapter(
            'I_Lightbox_Library',
            'A_Shutter_Lightbox_Library'
        );
    }
    
    function _enqueue_scripts()
    {
        wp_register_style(
            'shutter',
            PHOTOCRATI_GALLERY_SHUTTER_RELOADED_CSS_URL,
            array(),
            '2.0.1'
        );
        
        wp_register_script(
            'shutter',
            PHOTOCRATI_GALLERY_SHUTTER_RELOADED_JS_URL,
            array('jquery'),
            '2.0.1'
        );
        
        wp_enqueue_script('shutter');
        
        $data = array('image_path' => PHOTOCRATI_GALLERY_SHUTTER_IMAGES_URL);
        wp_localize_script('shutter', 'custom_vars', $data);
    }
}

new M_Shutter_Reloaded();
