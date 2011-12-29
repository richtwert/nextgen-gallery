<?php

define('MVC_MODULE_DIR', dirname(__FILE__));
define('MVC_TEMPLATE_DIR', path_join(MVC_MODULE_DIR, 'templates'));
include_once(path_join(MVC_MODULE_DIR, 'template_helper.php'));


class M_MVC extends C_Base_Module
{
    var $_router;
    
    function initialize()
    {
        parent::initialize(
        		"photocrati-mvc",
            "MVC Framework",
            "Provides an MVC architecture for the plugin to use",
            "0.1",
            "http://www.photocrati.com",
            "Photocrati Media",
            "http://www.photocrati.com"
        );
        
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
}

new M_MVC();
