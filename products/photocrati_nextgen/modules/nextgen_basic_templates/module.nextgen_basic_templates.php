<?php

/***
{
    Module: photocrati-nextgen_basic_templates,
    Depends: { photocrati-nextgen_gallery_display, photocrati-nextgen_basic_album }
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

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_Basic_Template_Form'
		);
	}

    function set_file_list()
    {
        return array(
            'adapter.nextgen_basic_template_form.php',
            'class.legacy_template_locator.php',
            'interface.legacy_template_locator.php'
        );
    }
}

new M_NextGen_Basic_Templates();
