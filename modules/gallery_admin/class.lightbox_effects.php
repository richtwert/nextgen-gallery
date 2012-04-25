<?php

class C_Lightbox_Effects extends C_Base_Form_Handler
{
    var $form_identifier = __CLASS__;
    var $factory_method  = 'lightbox_library';
    
    
    function define()
    {
        parent::define();
        $this->del_mixin('Mixin_Form_Handler_Overrides');
    }
    
    function get_config()
    {
        $factory = $this->_get_registry()->get_singleton_utility('I_Component_Factory');
        return $this->config = $factory->create(
            $this->factory_method,
            $this->handle_this_form() ? $this->param('settings') : array()
        );
    }
    
    
    function render_form($return=FALSE)
    {
        $config     = $this->get_config();
        $libraries  = $config->find_all();
        $default    = $config->find_default();
        
        return $this->render_partial('lightbox_effects_form', array(
            'config'    => $config,
            'libraries' => $libraries,
            'default'   => $default ? $default->name : ''
        ), $return);
    }
}