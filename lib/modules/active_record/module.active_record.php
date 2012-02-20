<?php

/***
	{
		Module: photocrati-active_record
	}
***/



class M_Active_Record extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
            'photocrati-active_record',
            'Active Record',
            'Provides the active record pattern for other modules to use',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_MVC_Controller', 'A_MVC_Controller_Helpers');
    }
}
new M_Active_Record();
