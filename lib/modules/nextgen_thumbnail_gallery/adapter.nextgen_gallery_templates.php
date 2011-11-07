<?php

class A_NextGen_Gallery_Templates extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'get_accordion_tabs',
            get_class(),
            get_class(),
            'add_display_template_tab'
        );
    }
    
    
    function add_display_template_tab()
    {
        // Get previous return value
        $tabs = $this->object->get_method_property(
            'get_accordion_tabs', 
            ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
        );
        
        // Add the new tab
        if (!$tabs) $tabs = array();
        array_splice($tabs, 3, 0, array(array(
           'label'      =>  _("Display Template (Optional - Post Specific)"),
           'callback'   => array(&$this, 'custom_display_template')
        )));
        
        // Return the tabs
        $this->object->set_method_property(
            'get_accordion_tabs',
            ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
            $tabs
        );
        
        return $tabs;
    }
    
    
    /**
     * Renders the accordion tab to collect a custom display template from the
     * user.
     */
    function custom_display_template()
    {
        // Get controller
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        $controller = $factory->create(
            'gallery_type_controller',
            NEXTGEN_BASIC_THUMBNAIL_GALLERY_TYPE,
            TRUE
        );
        unset($factory);
        
        // Execute action
        $controller->custom_display_template();
    }
}