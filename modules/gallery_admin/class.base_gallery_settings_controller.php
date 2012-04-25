<?php

/**
 * Methods which are expected to be overridden by subclasses 
 */
class Mixin_Base_Gallery_Settings_Overrides extends Mixin
{   
    function get_gallery_name(){}
    
    function configure_fields(){}
}


/**
 *  Overrides the render_form method from C_Base_Form_Handler   
 */
class Mixin_Base_Gallery_Settings_Renderer extends Mixin
{
    function render_form($return=FALSE)
    {
        $this->configure_fields();
        return $this->render_partial('fields', array('fields'=>$this->fields), $return);
    }
}


class Mixin_Base_Gallery_Settings extends Mixin
{
    function preview($return=FALSE)
    {
        return $this->render_partial('preview', array(
           'gallery_name'       =>  $this->object->get_gallery_name(),
           'preview_image_src'  =>  $this->object->static_url('preview.jpg')
        ), $return);
    }
}


class C_Base_Gallery_Settings_Controller extends C_Base_Form_Handler
{
    var $fields = array();
    var $config  = NULL;
    
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Base_Gallery_Settings_Overrides');
        $this->add_mixin('Mixin_Base_Gallery_Settings');
        $this->add_mixin('Mixin_Base_Gallery_Settings_Renderer');
        $this->remove_mixin('Mixin_Form_Handler_Overrides');
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
}

?>
