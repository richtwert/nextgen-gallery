<?php

/**
 * Provides methods for persisting to the database
 */
class Ext_Active_Record_Persistence extends Ext
{    
    /**
     * Saves the record to the database
     */
    function save()
    {
        $this->validate();
        
        if ($this->object->is_valid()) {
            if ($this->object->is_new()) $this->object->_create ();
            else $this->object->_update ();
        }
    }
    
    /**
     * Updates the existing record in the db
     */
    function _update()
    {
        $where = $this->object->db->prepare("{$this->object->id_field} = %s", $this->id());
        
        return $this->object->db->update($this->object->table_name, $this->object->properties, $where);
    }
    
    /**
     * Creates a new record in the db
     */
    function _create()
    {
        return $this->db->insert($this->object->table_name, $this->object->properties);
    }
    
    
    /**
     * Deletes the record from the database
     * @return type 
     */
    function delete()
    {
        $query = $wpdb->prepare(
            "DELETE FROM `{$this->object->table_name}` WHERE {$this->object->id_field} = %s",
            $this->object->id()
        );
        return $this->object->db->query($query);
    }
    
    /**
     * Expected to be overwritten
     */
    function validate() {}
}


/**
 * Provides database query methods for the Active Record table.
 * FYI, methods are NOT declared static because PHP 5.2 does not support late
 * binding
 */
class Ext_Active_Record_Query extends Ext
{   
    function find($id, $context=FALSE)
    {
        $results = $this->object->find_by("id is %d", array($this->object->id()), FALSE, 1, 1, $context);
        return array_pop($results);
    }
    
    
    function find_by($where, $vars=array(), $order='', $start=FALSE, $limit=FALSE, $context=FALSE)
    {
        $retval = array();
        
        // Generate query
        $sql = array($select = 'SELECT * FROM `'.$this->object->table_name.'`');
        if ($where)     $sql[] = $wpdb->prepare($where, $vars);
        if ($order_by)  $sql[] = 'ORDER BY '.str_replace(';', '', $order);
        if ($limit)     $sql[] = $wpdb->prepare("LIMIT %d", $limit);
        if ($start)     $sql[] = $wpdb->prepare("OFFSET %d", $start);
        $sql = $this->db->_substitute_id_for_id_field(implode(' ', $sql));
        

        // Convert each row to it's corresponding component
        foreach ($this->object->db->get_results($sql, ARRAY_A) as $row) {
            $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
            
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
    
    /**
     * @var C_Component_Registry 
     */
    var $_registry;
    
    
    function __construct($metadata=array(), $context=FALSE)
    {   
        parent::__construct();
        
        foreach ($metadata as $key => $value) {
            $this->properties[$key] = $value;
        }
        $this->db = $this->_registry->get_utility('I_Db', NULL, $context);
    }
    
    function define()
    {
        $this->add_ext('Ext_Active_Record_Validation');
        $this->add_ext('Ext_Active_Record_Persistence');
        $this->add_ext('Ext_Active_Record_Query');
        $this->implement('I_Active_Record');
    }
    
    /**
     * Defines a default return value for undefined properties 
     * @param string $property
     * @return FALSE 
     */
    function __get($property)
    {
        $retval = FALSE;
        if (isset($this->properties[$property])) {
            $retval = $this->properties[$property];
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
}