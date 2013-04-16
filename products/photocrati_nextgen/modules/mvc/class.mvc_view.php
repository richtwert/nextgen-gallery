<?php

class C_MVC_View extends C_Component
{
    var $_template = '';
    var $_engine   = '';
    var $_params   = array();
    
    function define($params, $context=FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Mvc_View_Instance_Methods');
    }
    
    /**
     * Initialize the view with some parameters
     * @param array $params
     * @param context $context
     */
    function initialize($template, $params=array(), $engine='php', $context=FALSE)
    {
        parent::initialize($context);
        $this->_template    = $template;
        $this->_params      = $params;
        $this->_engine      = $engine;
    }
}

class Mixin_Mvc_View_Instance_Methods extends Mixin
{
    /**
     * Returns the variables to be used in the template
     * @return array
     */
    function get_template_vars()
    {
        $retval = array();
     
        foreach ($this->object->_params as $key => $value) {
           if (strpos($key, '_template') !== FALSE) {
              $value = $this->object->get_template_abspath($value);
           }
           $retval[$key] = $value;
        }
        
        return $retval;
    }
    
    
    /**
     * Returns the abspath of the template to be rendered
     * @param string $key
     * @return string
     */
    function get_template_abspath($value=NULL)
    {
        if (!$value) $value = $this->object->_template;
        
        if ($value[0] == '/' && file_exists($value)) {
            // key is already abspath
        }
        else $value = $this->object->find_template_abspath($value);
        
        return $value;
    }
    
    
    
    /**
     * Renders the view (template)
     * @param string $__return
     * @return string|NULL
     */
    function render($__return=FALSE)
    {
        // We use underscores to prefix local variables to avoid conflicts wth
        // template vars
        $__content = NULL;
        extract($this->object->get_template_vars());
        
        if ($__return) ob_start();
        
        include($this->object->get_template_abspath());
        
        if ($__return) {
            $__content = ob_get_contents();
            ob_end_clean();
        }
        
        return $__content;
    }
    
    
    /**
     * Renders a sub-template for the view
     * @param string $template
     * @param string $__return
     * @return string|NULL
     */
    function include_template($template, $params = null, $__return=FALSE)
    {
    		if ($params == null) {
    			$params = array();
    		}
    		
    		$params['template_origin'] = $this->object->_template;
        
        $target = $this->object->get_template_abspath($template);
        $origin_target = $this->object->get_template_abspath($this->object->_template);
        
        if ($origin_target != $target)
        {
        	if (isset($params['target'])) {
        		unset($params['target']);
        	}
        	
        	extract($params);
        	
		      include($target);
        }
    }
    
    
    /**
     * Gets the absolute path of an MVC template file
     *
     * @param string $path
     * @param string $module
     * @return string
     */
   function find_template_abspath($path, $module=FALSE)
   {
       $fs       = $this->get_registry()->get_utility('I_Fs');
       $settings = $this->get_registry()->get_utility('I_Settings_Manager');

       // We also accept module_name#path, which needs parsing.
       if (!$module)
           list($path, $module) = $fs->parse_formatted_path($path);

       // Append the suffix
       $filename = $path . '.php';
       
       // Find the template
       $full_path = $fs->join_paths($settings->mvc_template_dirname, $filename);
       $retval    = $fs->find_abspath($full_path, $module);

       if (!$retval)
           throw new RuntimeException("{$full_path} is not a valid MVC template");

       return $retval;
   }

    /**
     * Adds a template parameter
     * @param $key
     * @param $value
     */
    function set_param($key, $value)
   {
       $this->object->_params[$key] = $value;
   }


    /**
     * Removes a template parameter
     * @param $key
     */
    function remove_param($key)
   {
       unset($this->object->_params[$key]);
   }

    /**
     * Gets the value of a template parameter
     * @param $key
     * @param null $default
     * @return mixed
     */
    function get_param($key, $default=NULL)
   {
       if (isset($this->object->_params[$key])) {
           return $this->object->_params[$key];
       }
       else return $default;
   }
}
