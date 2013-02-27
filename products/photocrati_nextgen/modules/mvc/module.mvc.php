<?php

/***
	{
		Module: photocrati-mvc
	}
***/

/**
 * TODO: The file below should be deprecated. We should use an example template
 * engine, such as Twig
 */
require_once('template_helper.php');

/**
 * Indicates that a clean exit occurred. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
	class E_Clean_Exit extends RuntimeException
	{

	}
}


class M_MVC extends C_Base_Module
{
	var $rethrow = FALSE;

    function define()
    {
        parent::define(
            "photocrati-mvc",
            "MVC Framework",
            "Provides an MVC architecture for the plugin to use",
            "0.1",
            "http://www.photocrati.com",
            "Photocrati Media",
            "http://www.photocrati.com"
        );
    }


    function initialize()
    {
		parent::initialize();
//        set_exception_handler(array(&$this, 'handle_exit'));
    }

    function _register_utilities()
    {
		$this->get_registry()->add_utility('I_Http_Response', 'C_Http_Response_Controller');
    }

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Settings_Manager', 'A_MVC_Settings');
		$this->get_registry()->add_adapter('I_Fs', 'A_MVC_Fs');
		$this->get_registry()->add_adapter('I_Router', 'A_MVC_Router');
	}

    function handle_exit($exception)
    {
        if (!($exception instanceof E_Clean_Exit))
			$this->rethrow = $exception;
    }

	function __destruct()
	{
		if ($this->rethrow)
			throw $this->rethrow;
	}
}

new M_MVC();
