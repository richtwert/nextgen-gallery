<?php

/***
{
	Module: photocrati-validation
}
***/

class M_Validation extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-validation',
			'Validation',
			'Provides validation support for objects',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

    function set_file_list()
    {
        return array(
            'mixin.validation.php'
        );
    }
}

new M_Validation();