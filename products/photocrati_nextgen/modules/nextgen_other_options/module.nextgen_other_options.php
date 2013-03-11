<?php
/*
{
	Module: photocrati-nextgen_other_options,
	Depends: { photocrati-nextgen_admin }
}
 */

define('NEXTGEN_OTHER_OPTIONS_SLUG', 'ngg_other_options');

class M_NextGen_Other_Options extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_other_options',
			'Other Options',
			'NextGEN Gallery Others Options Page',
			'0.2',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Page_Manager',
			'A_Other_Options_Page'
		);

		$this->get_registry()->add_adapter(
			'I_Form_Manager',
			'A_Other_Options_Forms'
		);

		$this->get_registry()->add_adapter(
			'I_Ajax_Controller',
			'A_Watermarking_Ajax_Actions'
		);
	}
}

new M_NextGen_Other_Options;