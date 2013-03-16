<?php
/*
{
	Module: photocrati-test
}
 */
class M_Test extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-test',
			'Test',
			'A test module',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Test_Controller', 'C_Test_Controller');
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_Test_Routes');
	}

    function set_file_list()
    {
        return array(
            'adapter.test_routes.php',
            'class.test_controller.php',
            'interface.test_controller.php'
        );
    }
}

new M_Test;