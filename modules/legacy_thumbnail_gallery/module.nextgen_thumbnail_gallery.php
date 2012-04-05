<?php

/***
	{
		Module: photocrati-nextgen_thumbnail
	}
***/

class M_NextGen_Thumbnail_Gallery extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_thumbnail',
            'NextGen Basic Thumbnails',
            "Provides support for NextGen's legacy thumbnail galleries",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
        
        C_Gallery_Type_Registry::add(
            $this->module_name,
            $this->module_description,
            'C_NextGen_Thumbnail_Gallery_Settings',
            'C_NextGen_Thumbnail_Gallery_View',
            'C_NextGen_Thumbnail_Gallery_Config'
        );
    }
    
    
    function initialize()
    {
    }
    
    
    /**
     * Registers an adapter to add a tab to the "Attach Gallery to Post"
     * accordion interface which allows the user to select a custom display
     * template
     */
    function _register_adapters()
    {
        $this->_get_registry()->add_adapter(
            'I_Component_Factory', 'A_NextGen_Thumbnail_Gallery_Factory'
        );
        
        $this->_get_registry()->add_adapter(
            'I_Resource_Loader', 'A_NextGen_Thumbnail_Gallery_Resources'
        );
    }
}

new M_NextGen_Thumbnail_Gallery();
