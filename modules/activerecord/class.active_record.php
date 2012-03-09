<?php

/**
 * Provides methods for persisting to the database
 */
class Mixin_Active_Record_Persistence extends Mixin
{
    /**
     * @var Wordpress_Db
     */
    var $db;
    
    function initialize()
    {
        $this->db = &$this->object->db;
    }
    
    
    function update_properties($updates=array())
    {
        if ($this->object->has_method('set_defaults')) {
            $updates = $this->object->set_defaults($updates);
        }
        
        $this->object->properties = $this->array_merge_assoc(
            $this->properties, $updates
        );
    }
    
    /**
     * Saves the record to the database
     */
    function save($updates=array())
    {
        if ($updates) $this->update_properties($updates);
        
        $this->object->validate();
        
        $retval = FALSE;
        
        if ($this->object->is_valid()) {
            if ($this->object->is_new()) {
                $retval = $this->object->_create ();
            }
            else {
                $retval = $this->object->_update ();
            }
        }
        
        return $retval;
    }
    
    /**
     * Updates the existing record in the db
     */
    function _update()
    {   
        $props = array();
        foreach ($this->object->properties as $key => $value) {
            $props[$key] = $this->object->try_serialize($value);
        }
        
        return $this->db->update($this->object->table_name, $props, array(
            $this->object->id_field => $this->object->id()
        ));
    }
    
    /**
     * Creates a new record in the db
     */
    function _create()
    {   
        if ($this->db->insert($this->object->table_name, $this->object->properties)) {
            $this->object->__set($this->object->id_field, $this->db->insert_id);
        }
        return ($this->db->num_rows > 0 ? TRUE : FALSE);
    }
    
    
    /**
     * Deletes the record from the database
     * @return type 
     */
    function delete()
    {
        $query = $wpdb->prepare(
            "DELETE FROM `{$this->object->table_name}` WHERE {$this->object->id_field} = %s",
            (string)$this->object->id()
        );
        return $this->object->db->query($query);
    }
}


/**
 * Provides database query methods for the Active Record table.
 * FYI, methods are NOT declared static because PHP 5.2 does not support late
 * binding
 */
class Mixin_Active_Record_Query extends Mixin
{   
    function find($id, $context=FALSE)
    {
        $results = $this->object->find_by("id = %s", array($id), FALSE, 0, 1, $context);
        return array_pop($results);
    }
    
    
    function find_by($where, $vars=array(), $order='', $start=FALSE, $limit=FALSE, $context=FALSE)
    {
        $retval = array();
        
        // Generate query
        $sql = array($select = 'SELECT * FROM `'.$this->object->table_name.'`');
        if ($where)                     $sql[] = ' WHERE '.$this->db->prepare($where, $vars);
        if ($order)                     $sql[] = 'ORDER BY '.str_replace(';', '', $order);
        if ($limit && $limit != -1)     $sql[] = $this->db->prepare("LIMIT %d", $limit);
        if ($start && $start != -1)     $sql[] = $this->db->prepare("OFFSET %d", $start);
        $sql = $this->db->_substitute_id_for_id_field(implode(' ', $sql), $this->object->id_field);
        
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        
        // Convert each row to it's corresponding component
        foreach ($this->object->db->get_results($sql, ARRAY_A) as $row) {
            
            $retval[] = $factory->create(
               $this->object->object_name,
               $row,
               $context 
            );
            unset($row);
        }
        
        return $retval;
    }
    
    
    function find_all($order='', $start=FALSE, $limit=FALSE, $context=FALSE)
    {
        return $this->object->find_by(FALSE, FALSE, $order, $start, $limit, $context);
    }
    
    
    function find_first($where=FALSE, $vars=array(), $order='', $context=FALSE)
    {
        return array_pop($this->object->find_by(FALSE, FALSE, $order, FALSE, 1));
    }
}

/**
 * Provides an object-to-relational mapping tool
 */
abstract class C_Active_Record extends C_Component
{    
    var $table_name;
    var $db;
    var $id_field = 'id';
    var $object_name = 'gallery';
    var $properties = array();
    
    
    function define()
    {
    		parent::define();
    		
        $this->add_mixin('Mixin_Active_Record_Validation');
        $this->add_mixin('Mixin_Active_Record_Persistence');
        $this->add_mixin('Mixin_Active_Record_Query');
        $this->implement('I_Active_Record');
    }
    
    function initialize($metadata=array(), $context=FALSE)
    {   
        parent::initialize($context);
        $this->update_properties($metadata);
        $this->db = $this->_registry->get_utility('I_Db', NULL, $context);
    }
    
    /**
     * Defines a default return value for undefined properties 
     * @param string $property
     * @return FALSE 
     */
    function &__get($property)
    {
        $retval = NULL;
        if (isset($this->properties[$property])) {
            $retval = &$this->properties[$property];
        }
        return $retval;
    }
    
    /**
     * Sets a property for the object
     * @param string $property
     * @param mixed $value
     * @return mixed 
     */
    function __set($property, $value)
    {
        $this->properties[$property] = $value;
        return $value;
    }
    
    
    /**
     * Sets all of the properties at once
     * @param array $value
     */
    function set_properties($value)
    {
        $this->properties = $value;
    }
    
    /**
     * Returns the unique id of the record
     * @return type 
     */
    function id()
    {
        return $this->__get($this->id_field);
    }
    
    
    /**
     * Returns TRUE if the record is a new record, and hasn't been persisted yet
     * @return boolean
     */
    function is_new()
    {
        return (!$this->id());
    }
    
    /**
     * Tries to unserialize an attribute, unless it's an array 
     */
    function try_unserialize($value)
    {   
        if (is_string($value) && strlen($value) > 1 && !is_numeric($value)) {
            // I've been having an issue with the first character being a binary
            // value. Not sure why. Need to investigate further. This is just a
            // hack as haven't determined root cause.
            // if ($value[0] != 'a') $value[0] = 'a';
            return photocrati_gallery_plugin_unserialize($value);
        }
        else
            return $value;
            
    }
    
    
    /**
     * Trys to serialize a value, unless it's a string 
     */
    function try_serialize($value)
    {
    	// XXX probably best to always serialize for consistence?
        if (is_array($value) || is_object($value)) {
            return photocrati_gallery_plugin_serialize($value);
        }
        else
            return $value;
    }
}
