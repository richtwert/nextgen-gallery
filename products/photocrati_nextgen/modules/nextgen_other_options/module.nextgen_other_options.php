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

    function set_file_list()
    {
        return array(
            'adapter.image_options_form.php',
            'adapter.lightbox_manager_form.php',
            'adapter.miscellaneous_form.php',
            'adapter.other_options_controller.php',
            'adapter.other_options_forms.php',
            'adapter.other_options_page.php',
            'adapter.reset_form.php',
            'adapter.roles_form.php',
            'adapter.size_options_form.php',
            'adapter.styles_form.php',
            'adapter.thumbnail_options_form.php',
            'adapter.watermarking_ajax_actions.php',
            'adapter.watermarks_form.php'
        );
    }
}

new M_NextGen_Other_Options;