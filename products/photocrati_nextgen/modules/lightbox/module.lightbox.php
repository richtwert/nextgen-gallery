<?php

/***
    {
        Module: photocrati-lightbox,
        Depends: { photocrati-nextgen_admin }
    }
***/

define('NEXTGEN_LIGHTBOX_OPTIONS_SLUG', 'ngg_lightbox_options');
define('NEXTGEN_LIGHTBOX_ADVANCED_OPTIONS_SLUG', 'ngg_lightbox_advanced_options');

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
            $this->get_registry()
                 ->add_adapter('I_Form', 'A_Lightbox_Library_Form', $lib->name);
            $this->get_registry()
                 ->get_utility('I_Form_Manager')
                 ->add_form(NEXTGEN_LIGHTBOX_ADVANCED_OPTIONS_SLUG, $lib->name);
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

    function get_type_list()
    {
        return array(
            'A_Lightbox_Factory' => 'adapter.lightbox_factory.php',
            'A_Lightbox_Installer' => 'adapter.lightbox_installer.php',
            'A_Lightbox_Library_Form' => 'adapter.lightbox_library_form.php',
            'C_Lightbox_Library' => 'class.lightbox_library.php',
            'C_Lightbox_Library_Mapper' => 'class.lightbox_library_mapper.php',
            'I_Lightbox_Library' => 'interface.lightbox_library.php',
            'I_Lightbox_Library_Mapper' => 'interface.lightbox_library_mapper.php'
        );
    }
}

new M_Lightbox();
