<?php

class M_NextGen_ImageBrowser_Gallery extends C_Base_Module
{
    function initialize($context=FALSE)
    {
        parent::initialize(
        		'photocrati-gallery-nextgen-imagebrowser',
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
            'C_NextGen_ImageBrowser_View'
        );
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Component_Factory', 'A_NextGen_ImageBrowser_Factory');
    }
}
new M_NextGen_ImageBrowser_Gallery();
