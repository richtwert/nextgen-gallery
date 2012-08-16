<?php

/***
	{
		Module: photocrati-nextgen_imagebrowser
	}
***/

class M_NextGen_ImageBrowser_Gallery extends C_Base_Module
{
    function define($context=FALSE)
    {
        parent::define(
            'photocrati-nextgen_imagebrowser',
            'NextGen Basic ImageBrowser',
            "Provides the NextGen Basic ImageBrowser gallery type",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com',
            $context
        );
        
        C_Gallery_Type_Registry::add(
            $this->module_name,
            $this->module_description,
            'C_NextGen_ImageBrowser_Settings',
            'C_NextGen_ImageBrowser_View',
            'C_NextGen_ImageBrowser_Config'
        );
    }
    
    
    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_NextGen_ImageBrowser_Factory');
        $this->get_registry()->add_adapter('I_Resource_Loader', 'A_NextGen_ImageBrowser_Resources');
    }
}
new M_NextGen_ImageBrowser_Gallery();
