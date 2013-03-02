<?php
/*
{
	Module: photocrati-nextgen_other_options,
	Depends: { photocrati-nextgen_admin }
}
 */
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

	function initialize() {
		parent::initialize();
		$this->get_registry()->get_utility('I_Page_Manager')->add(
			'ngg_other_options',
			'A_Other_Options_Controller',
			NGGFOLDER
		);
	}
}

new M_NextGen_Other_Options;