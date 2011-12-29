<?php

/**
 * Methods which are expected to be overridden by subclasses 
 */
class Mixin_Base_Gallery_Settings_Overrides extends Mixin
{
    function get_config(){}
    
    function get_gallery_name(){}
    
    function configure_fields(){}
}


class Mixin_Base_Gallery_Settings extends Mixin
{
    function preview()
    {
        $this->render_partial('preview', array(
           'gallery_name'       =>  $this->object->get_gallery_name(),
           'preview_image_src'  =>  $this->object->static_url('preview.jpg')
        ));
    }
}


class C_Base_Gallery_Settings_Controller extends C_MVC_Controller
{
    var $fields = array();
    var $config  = NULL;
    var $form_identifier = __CLASS__;
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Base_Gallery_Settings_Overrides');
        $this->add_mixin('Mixin_Base_Gallery_Settings');
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
        $this->configure_fields();
        $this->render_partial('fields', array('fields'=>$this->fields));
    }
    
    
    function render_field($field, $args=array())
    {   
        $field['config'] = $this->config;
        $this->render_partial(
            $field['template'], 
            $this->array_merge_assoc($field, $args)
        );
    }
    
    
    function append_field($field)
    {
        $this->fields[$field['id']] = $field;
    }
    
    
    function prepend_field($field)
    {
        $fields = array($field['id'] => $field);
        $this->fields = array_merge($fields, $this->fields);
    }
    
    
    function render_form_handle_tag()
    {
        $this->render_partial('form_handle_tag', array(
            'value'=>$this->form_identifier
        ));
    }
    
    
    function handle_this_form()
    {
        return ($this->param('form') == $this->form_identifier);
    }
}

?>
