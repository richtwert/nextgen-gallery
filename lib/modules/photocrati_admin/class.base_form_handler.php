<?php

class Mixin_Form_Handler_Overrides extends Mixin
{
    function render_form(){}
    
    function get_config(){}
}

class C_Base_Form_Handler extends C_MVC_Controller
{
    var $form_identifier = __CLASS__;    
    
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Form_Handler_Overrides');
    }
    
    function initialize($context=FALSE)
    {
        parent::initialize($context);
        $this->config = $this->get_config();
    }
    
    function index()
    {   
        $message = FALSE;
        
        if ($this->is_post_request() && $this->handle_this_form()) {
            if ($this->config->save()) $message = _e("Settings saved successfully");
        }
        
        echo $this->show_errors_for($this->config);
        echo $this->render_form_handle_tag();
        $this->render_form();
    }
    
    
    function handle_this_form()
    {
        return ($this->param('form') == $this->form_identifier);
    }
    
    function render_form_handle_tag()
    {
        $this->render_partial('form_handle_tag', array(
            'value'=>$this->form_identifier
        ));
    }
}