<?php

class C_Photocrati_Admin extends C_Base_Admin_Controller
{
    
    function galleries()
    {
        $this->render_partial('test');
    }
    
    
    function gallery_settings()
    {
        $tabs = array();
        
        foreach (C_Gallery_Type_Registry::get_all() as $name => $properties) {
            ob_start();
            $klass = $properties['admin_controller'];
            $controller = new $klass('photocrati_admin_gallery_settings');
            $this->_registry->apply_adapters($controller);
            call_user_func(array($controller, 'index'));
            $tab = ob_get_contents();
            ob_end_clean();
            $tabs[$name] = $this->render_partial(
                'accordion_tab', 
                array('tab' => $tab),
                TRUE
            ); 
        }
        
        $this->render_partial('gallery_settings', array(
            'accordion' => $this->_render_accordion($tabs, TRUE)
        ));
      
    }
}