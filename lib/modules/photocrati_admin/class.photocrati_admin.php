<?php

class Mixin_Photocrati_Admin_Overrides extends Mixin
{
    /**
     * Returns a list of form controllers, used to populate the "Other Options"
     * page
     * @return array
     */
    function _get_other_options_forms()
    {
        return array(
            _('Lightbox Effects') => 'C_Lightbox_Effects'
        );
    }
}


class C_Photocrati_Admin extends C_Base_Admin_Controller
{
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Photocrati_Admin_Overrides');
        $this->implement('I_Admin_Controller');
    }
    
    
    function galleries()
    {
        $this->render_partial('test');
    }
    
    
    function _render_tab($klass, $context=FALSE)
    {
        ob_start();
        $controller = new $klass($context);
        $this->_registry->apply_adapters($controller);
        call_user_func(array($controller, 'index'));
        $tab = ob_get_contents();
        ob_end_clean();
        
        return $this->render_partial(
            'accordion_tab',
            array('tab' => $tab),
            TRUE
        );
    }
    
    
    function _render_page($title, $tabs)
    {
        $this->render_partial('admin_page', array(
            'title' => $title,
            'accordion' => $this->_render_accordion($tabs, TRUE)
        ));
    }
    
    
    function other_options()
    {
        $tabs = array();
        
        die(print_r($this->_get_other_options_forms()));
        
        foreach ($this->_get_other_options_forms() as $name => $klass) {
            $tabs[$name] = $this->_render_tab($klass);
        }
        
        $this->_render_page(_('Other Options'), $tabs);
    }
    
    
    function gallery_settings()
    {
        $tabs = array();
        
        foreach (C_Gallery_Type_Registry::get_all() as $name => $properties) {
            $tabs[$name] = $this->_render_tab(
                $properties['admin_controller'],
                'photocrati_admin_gallery_settings'
            );
        }
        
        $this->_render_page(_('Gallery Settings'), $tabs);
    }
}