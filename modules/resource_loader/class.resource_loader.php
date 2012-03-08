<?php
class Mixin_Resource_Loader extends Mixin
{   
    function enqueue_script($name, $pattern=FALSE)
    {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        
        $this->object->add_resource(
            'enqueued_scripts',
             $name.'_js',
             array('args'=>$args, 'pattern'=>$pattern)   
        );
    }
    
    function enqueue_stylesheet($name, $pattern=FALSE)
    {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        
        $this->object->add_resource(
            'enqueued_styles',
             $name.'_css',
             array('args'=>$args, 'pattern'=>$pattern)   
        );
    }
    
    
    function add_resource($type, $name, $properties)
    {
        // Ensure that we're bootstrapped
        $request_id = $this->object->ensure_session($type);
        
        $resource = photocrati_gallery_plugin_serialize(array($name, $properties));
        
        if (!in_array($resource, $_SESSION[$type][$request_id])) {
            $_SESSION[$type][$request_id][] = $resource;
        }
    }
    
    
    function ensure_session($type, $id=FALSE)
    {
        // Ensure that we're bootstrapped
        $request_id = $id ? $id : PHOTOCRATI_GALLERY_MOD_RESOURCE_LOADER_ID;
        
        // Create resource container
        if (!isset($_SESSION[$type]) OR !is_array($_SESSION[$type]))
            $_SESSION[$type] = array();
        
        if (!isset($_SESSION[$type][$request_id]) OR !is_array($_SESSION[$type][$request_id]))
            $_SESSION[$type][$request_id] = array();
        
        return $request_id;
    }
    
    
    function delete_session($type, $id)
    {
        unset($_SESSION[$type][$id]);
    }
    
    /**
     * A controller action which displays the dynamic stylesheet 
     */
    function dynamic_styles()
    {
        header('Content-Type: text/css');
        $this->load_enqueued_resources('enqueued_styles', $this->param('id'));
        $this->delete_session('enqueued_styles', $this->param('id'));
    }
    
    /**
     * A controller action which displays the dynamic scripts 
     */
    function dynamic_scripts()
    {
        header('Content-Type: application/javascript');
        $this->load_enqueued_resources('enqueued_scripts', $this->param('id'));
        $this->delete_session('enqueued_scripts', $this->param('id'));
    }
    
    /**
     * Loads all enqueued resources
     * @param type $resources 
     */
    function load_enqueued_resources($type, $id)
    {
        // Ensure that we're bootstrapped
        $this->object->ensure_session($type, $id);
        
        // Load all enqueued resources
        foreach ($_SESSION[$type][$id] as $resource) {
            $resource = photocrati_gallery_plugin_unserialize($resource);
            $name = $resource[0];
            extract($resource[1]);
            $load = FALSE;
            if ($this->object->has_method($name)) {
                
                // Determine if the resource should be included
                if ($pattern && preg_match($pattern, $_SERVER['REQUEST_URI'])) {
                    $load = TRUE;
                }
                elseif (!$pattern) {
                    $load = TRUE;
                }
                
                // Include?
                if ($load) call_user_func_array(
                    array(&$this, $name), 
                    $args
                );
                
            }
        }
    }
}


class C_Resource_Loader extends C_MVC_Controller
{   
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Resource_Loader');
        $this->implement('I_Resource_Loader');
    }
    
    function initialize($context = FALSE)
    {
        parent::initialize($context);
        if (!session_id()) session_start();
    }
}
