<?php

/***
    {
        Module: photocrati-shutter_reloaded
    }
 ***/

define('PHOTOCRATI_GALLERY_SHUTTER_IMAGES_URL', path_join(
    PHOTOCRATI_GALLERY_PLUGIN_MODULE_URL,
    basename(dirname(__FILE__)).'/static/shutter/images/'
));

class M_Shutter_Reloaded extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-shutter_reloaded',
            'Shutter Reloaded',
            'Provides integration with the Shutter Reloaded lightbox plugin',
            '0.1',
            'http://www.laptoptips.ca/javascripts/shutter-reloaded/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
		$this->get_registry()->add_adapter(
			'I_NextGen_Activator',
			'A_Shutter_Reloaded_Library_Activation'
		);
    }
}

new M_Shutter_Reloaded();
