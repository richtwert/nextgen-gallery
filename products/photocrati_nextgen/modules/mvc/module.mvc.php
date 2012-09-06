<?php

/***
	{
		Module: photocrati-mvc
	}
***/

define('MVC_MODULE_DIR', dirname(__FILE__));
define('MVC_TEMPLATE_DIR', path_join(MVC_MODULE_DIR, 'templates'));
require_once(path_join(MVC_MODULE_DIR, 'template_helper.php'));

/**
 * Indicates that a clean exit occured. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
	class E_Clean_Exit extends RuntimeException
	{

	}
}


class M_MVC extends C_Base_Module
{
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
        $this->get_registry()->add_utility('I_Router', 'C_Router');
    }

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Activator_Rendering');
	}

    function _register_hooks()
    {
        add_action('wp_loaded', array(&$this, 'route'), 99);
    }


	function route()
	{
		$router = $this->get_registry()->get_utility('I_Router');
		$router->route();
	}

    function handle_exit($exception)
    {
        if (!($exception instanceof E_Clean_Exit)) {
            throw $exception;
        }
    }
}

new M_MVC();
