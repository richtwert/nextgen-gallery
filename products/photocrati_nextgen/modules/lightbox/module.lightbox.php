<?php

/***
    {
        Module: photocrati-lightbox,
        Depends: { photocrati-nextgen_admin }
    }
***/

define('NEXTGEN_LIGHTBOX_OPTIONS_SLUG', 'ngg_lightbox_options');

class M_Lightbox extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-lightbox',
            'Lightbox',
            _("Provides integration with JQuery's lightbox plugin"),
            '0.1',
            'http://leandrovieira.com/projects/jquery/lightbox/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function initialize()
    {
        parent::initialize();

        // Add a configuration form to each library
        foreach ($this->get_registry()->get_utility('I_Lightbox_Library_Mapper')->find_all() as $lib) {
            // allow libraries with their own display settings to configure their own forms
            if (!empty($lib->display_settings)) { continue; }
            $this->get_registry()
                 ->add_adapter('I_Form', 'A_Lightbox_Library_Form', $lib->name);
            $this->get_registry()
                 ->get_utility('I_Form_Manager')
                 ->add_form(NEXTGEN_LIGHTBOX_OPTIONS_SLUG, $lib->name);
        }
    }

	function _register_utilities()
	{
        // Provides a utility to perform CRUD operations for Lightbox libraries
		$this->get_registry()->add_utility(
			'I_Lightbox_Library_Mapper',
			'C_Lightbox_Library_Mapper'
		);
	}

    function _register_adapters()
    {
        // Provides factory methods for instantiating lightboxes
		$this->get_registry()->add_adapter('I_Component_Factory', 'A_Lightbox_Factory');

        // Provides an installer for lightbox libraries
        $this->get_registry()->add_adapter('I_Installer', 'A_Lightbox_Installer');
    }

    function set_file_list()
    {
        return array(
            'adapter.lightbox_factory.php',
            'adapter.lightbox_installer.php',
            'adapter.lightbox_library_form.php',
            'class.lightbox_library.php',
            'class.lightbox_library_mapper.php',
            'interface.lightbox_library.php',
            'interface.lightbox_library_mapper.php'
        );
    }
}

new M_Lightbox();
