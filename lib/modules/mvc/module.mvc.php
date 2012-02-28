<?php

/***
	{
		Module: photocrati-mvc
	}
***/

define('MVC_MODULE_DIR', dirname(__FILE__));
define('MVC_TEMPLATE_DIR', path_join(MVC_MODULE_DIR, 'templates'));
include_once(path_join(MVC_MODULE_DIR, 'template_helper.php'));

/**
 * Class used to indicate a clean exit occured
 */
class CleanExitException extends Exception
{
    function __construct()
    {
        parent::__construct('Clean exit forced', 0);
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
        $this->_router = $this->_registry->get_singleton_utility('I_Router');
    }
    
    function _register_utilities()
    {
        $this->_registry->add_utility('I_Router', 'C_Router');
    }
    
    function _register_hooks()
    {
        add_action('wp_loaded', array(&$this->_router, 'route'), 99);
    }
    
    function handle_exit($exception)
    {
        if (!($exception instanceof CleanExitException)) {
            trigger_error($exception);
        }
    }
}

new M_MVC();
