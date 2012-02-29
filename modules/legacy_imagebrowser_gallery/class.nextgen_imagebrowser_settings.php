<?php

class Mixin_NextGen_ImageBrowser_Settings extends Mixin
{   
    function get_config()
    {
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        return $factory->create(
            'nextgen_imagebrowser_config',
            $this->handle_this_form()? $this->param('settings') : array()
        );
    }
    
    
    function get_gallery_name()
    {
        return _('NextGen Basic ImageBrowser');
    }
}


class C_NextGen_ImageBrowser_Settings extends C_Base_Gallery_Settings_Controller
{
    var $form_identifier = __CLASS__;
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_NextGen_ImageBrowser_Settings');
        //$this->remove_mixin('Mixin_Base_Gallery_Settings_Overrides');
    }
}