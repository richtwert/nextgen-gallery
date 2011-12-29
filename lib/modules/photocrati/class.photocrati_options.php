<?php

/**
 * Provides specific ways of setting and getting NextGen properties
 */
class Mixin_NextGen_Options extends Mixin
{
    var $nextgen_options = array(
        'storage_dir'
    );
    
    /**
     * Gets or sets a NextGen option
     */
    function get_or_set_option()
    {
        $retval = NULL;
        global $ngg;
        $args = func_get_args();
        $property = $args[0];
        
        switch ($property) {
            case 'storage_dir':
                $property = 'gallerypath';
            default:
                if (isset($args[1])) {
                    $ngg->options[$property] = $args[1];
                    update_option('ngg_options', $ngg->options);
                }
                $retval = $ngg->options[$property];
                break;
        }
        
        return $retval;
    }
    
    
    function is_nextgen_option($property)
    {
        return in_array($property, $this->nextgen_options);
    }
}

/**
 * This class provides access to internal Photocrati options
 * You should probably use C_Photocrati_Options instead, which is a
 * wrapper for this class and provides integration with NextGen Legacy
 */
class C_Photocrati_Internal_Options extends C_Base_Component_Config
{   
    function initialize($settings=FALSE, $context=FALSE)
    {
        
        parent::initialize($settings, $context);
        if (!$this->settings) {
            // Set defaults    
        }       
    }
}


/**
 * Photocrati Options
 */
class C_Photocrati_Options extends C_Component
{
    static $_instance = NULL;
    var $_internal_options = NULL;
    
    function define()
    {
        $this->implement('I_Photocrati_Options');
        $this->add_mixin('Mixin_NextGen_Options');
    }
    
    
    function initialize($context=FALSE)
    {
        parent::initialize($context);
        $factory = $this->_registry->get_singleton_utility('I_Component_Factory');
        $this->_internal_options = $factory->create('photocrati_options');
        unset($factory);        
    }
    
    
    /**
     * This is a method that shouldn't need to be called directly
     */
    function _save()
    {
        $retval = TRUE;
        
        if (!$this->_batch) $retval = $this->_internal_options->save();
        
        return $retval;
    }
    
    
    /*
     * Provides an abstraction between NextGen and Photocrati options. 
     */
    function __get($property)
    {
        $retval = NULL;
        
        // An extension might have provided a way of retrieving this propery
        // from NextGen
        if ($this->is_nextgen_option($property)) {
            $retval = $this->call_method('get_or_set_option', array($property));
        }
        
        // Must be a Photocrati option. Get from the internal options
        else {
            $retval = $this->_internal_options->$property;
        }
        
        return $retval;
    }
    
    
    /**
     * Provides an abstraction between NextGen and Photocrati options
     */
    function __set($property, $value)
    {
        $retval = NULL;
        
        // An extension might have provided a way of setting this propery
        if ($this->is_nextgen_option($property)) {
            $retval = $this->call_method('get_or_set_option', array($property, $value));
        }
        
        // Must be a Photocrati option. Set the internal option
        else {
            $this->_internal_options->$property = $value;
            $this->_save();
        }
        
        return $retval;
    }
    
    
    /**
     * Provides a means to set multiple options at once, which is more efficient
     * than setting individual options since the save method is
     * @param type $properties 
     */
    function set($properties=array())
    {
        $retval = NULL;
        $this->batch = TRUE;
        
        try {
            // Iterate through each property, and set
            // the option.
            // Continue to set the return value until FALSE
            // has been returned. Once FALSE, always FALSE.
            foreach ($properties as $p => $v) {
                $return = $this->__set($p, $v);
                if (is_null($retval)) $retval = $return;
                elseif (retval) $retval = $return;
            }
        }
        catch (Exception $e) {
            // We only have a try/catch to ensure that
            // the batch flag is set below
        }
        $this->batch = FALSE;
        
        return $retval;
    }
    
    
    /**
     * Returns an instantation of the C_Photocrati_Options class
     */
    static function get_instance()
    {
        if (!self::$_instance) self::$_instance = new C_Photocrati_Options();
        return self::$_instance;
    }
}
