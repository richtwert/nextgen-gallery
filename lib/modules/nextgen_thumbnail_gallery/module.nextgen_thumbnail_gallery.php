<?php

/***
	{
		Module: photocrati-nextgen_thumbnail,
                Depends: { photocrati-attach_from_post_type }
	}
***/

class M_NextGen_Thumbnail_Gallery extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
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
            'C_NextGen_Thumbnail_Gallery_View'
        );
    }
    
    
    /**
     * Registers an adapter to add a tab to the "Attach Gallery to Post"
     * accordion interface which allows the user to select a custom display
     * template
     */
    function _register_adapters()
    {
        $this->_registry->add_adapter(
            'I_Component_Factory', 'A_NextGen_Thumbnail_Gallery_Factory'
        );
        
        $this->_registry->add_adapter(
            'I_Resource_Loader', 'A_NextGen_Thumbnail_Gallery_Resources'
        );
    }
}

new M_NextGen_Thumbnail_Gallery();
