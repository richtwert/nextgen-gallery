<?php

/***
 {
	Module: photocrati-security
 }
 ***/

class M_Security extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-security',
			'Security',
			'Provides utilities to check for credentials and security',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Component_Factory', 'A_Security_Factory');
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Security_Manager', 'C_WordPress_Security_Manager');
	}

    function set_file_list()
    {
        return array(
            'adapter.security_factory.php',
            'class.security_actor.php',
            'class.security_manager.php',
            'class.security_token.php',
            'class.wordpress_security_actor.php',
            'class.wordpress_security_manager.php',
            'class.wordpress_security_token.php',
            'interface.security_actor.php',
            'interface.security_manager.php',
            'interface.security_token.php'
        );
    }

}

new M_Security();
