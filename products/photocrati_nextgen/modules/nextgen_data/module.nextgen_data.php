<?php

/***
{
		Module: photocrati-nextgen-data,
		Depends: { photocrati-nextgen_settings, photocrati-datamapper }
}
***/

class M_NextGen_Data extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen-data',
            'NextGEN Data Tier',
            "Provides a data tier for NextGEN gallery based on the DataMapper module",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_NextGen_Data_Factory');
		$this->get_registry()->add_adapter('I_CustomPost_DataMapper', 'A_Attachment_DataMapper', 'attachment');
        $this->get_registry()->add_adapter('I_NextGen_Deactivator', 'A_NextGen_Data_Deactivation');
    }


    function _register_utilities()
    {
		$this->get_registry()->add_utility('I_Gallery_Mapper', 'C_Gallery_Mapper');
		$this->get_registry()->add_utility('I_Image_Mapper', 'C_Image_Mapper');
        $this->get_registry()->add_utility('I_Album_Mapper', 'C_Album_Mapper');
        $this->get_registry()->add_utility('I_Transients', 'C_NextGen_Transients');
        $this->get_registry()->add_utility('I_Gallery_Storage', 'C_Gallery_Storage');
    }
}
new M_NextGen_Data();
