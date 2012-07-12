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
    var $_router;

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
        set_exception_handler(array(&$this, 'handle_exit'));
        $this->_router = $this->_get_registry()->get_singleton_utility('I_Router');
    }

    function _register_utilities()
    {
        $this->_get_registry()->add_utility('I_Router', 'C_Router');
    }

    function _register_hooks()
    {
        add_action('wp_loaded', array(&$this->_router, 'route'), 99);
    }

    function handle_exit($exception)
    {
        if (!($exception instanceof E_Clean_Exit)) {
            throw $exception;
        }
    }
}

new M_MVC();
