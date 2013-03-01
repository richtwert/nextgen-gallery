<?php

/***
{
    Module: photocrati-nextgen_basic_templates,
    Depends: { photocrati-nextgen_gallery_display }
}
 ***/

class M_NextGen_Basic_Templates extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_basic_templates',
			'NextGen Basic Templates',
			'Provides a NextGen-Legacy compatible thumbnail gallery for NextGEN Gallery',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

    function _register_utilities()
    {
        $this->get_registry()->add_utility(
            'I_Legacy_Template_Locator',
            'C_Legacy_Template_Locator'
        );
    }
}

new M_NextGen_Basic_Templates();
