<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * This is the meat and potatoes of the plugin.
 * 
 * This plugin uses a component architecture, heavily based on what's used
 * in Zope 3: http://www.muthukadan.net/docs/zca.html
 */

class C_Photocrati_Gallery_Plugin extends C_Base_Module
{   
    function define()
    {
        parent::define('photocrati-gallery-core', 'Photocrati Core');
        
        $this->_load_modules();
    }
    
    
    function _register_utilities()
    {
        $this->_registry->add_utility('I_Component_Factory', 'C_Component_Factory');
        $this->_registry->add_utility('I_Db',                'C_WordPress_Db');
    }
    
    /**
     * Loads all modules
     */
    function _load_modules()
    {
    	$this->_registry->add_module_path(PHOTOCRATI_GALLERY_MODULE_DIR, true, true);
    	$this->_registry->add_module_path(PHOTOCRATI_GALLERY_PRODUCT_DIR, true, true);
    	
    	$this->_registry->initialize_all_modules();
    }
}
