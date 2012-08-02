<?php

class Mixin_Lightbox_Library extends Mixin
{
    function set_default()
    {
        $this->object->__set('default', TRUE);
    }

    function is_default()
    {
        return $this->object->__get('default');
    }


    function _update()
    {
        $libraries = $this->object->_load_libraries();
        $properties = $this->object->properties;
        $library_name = $properties['name'];
        $default = $this->object->is_default() ? $library_name : FALSE;
        unset($properties['default']);
        $libraries['default'] = $default;
        $libraries[$library_name] = $properties;

        return update_option(get_class($this->object), $libraries);
    }

    function _create()
    {
        return $this->object->_update();
    }

    function _load_libraries($remove_default=TRUE)
    {
        // Get the libraries from the options table
        $retval = get_option(get_class($this->object), array('custom' => array()));

        if (isset($retval['default']) && $retval['default']) {
            $default = $retval['default'];
            $retval[$default]['default'] = TRUE;
        }
        if ($remove_default) unset($retval['default']);

        return $retval;
    }


    function _hatch_lightbox_library($factory, $library_name, $properties, $context=FALSE)
    {
        foreach ($properties as $key => $value) {
            $properties[$key] = stripslashes($value);
        }

        return $factory->create(
            $this->object->factory_method,
            array_merge(array('name'=>$library_name), $properties),
            $context
        );
    }


    function find($library_name, $context=FALSE)
    {
        $libraries = $this->object->_load_libraries();
        if (isset($libraries[$library_name])) {

            // Get library properties
            $properties = $libraries[$library_name];

            // Create a factory to hatch C_Lightbox_Library objects
            $factory = $this->object->_get_registry()->get_utility('I_Component_Factory');

            // Return an object
            return $this->object->_hatch_lightbox_library($factory, $library_name, $properties);

        }
        else return NULL;
    }

    function find_by($where, $vars=array(), $order='', $start=FALSE, $limit=FALSE, $context=FALSE)
    {
        throw new Exception ("Not Implemented");
    }


    function find_default()
    {
        $retval = NULL;
        $libraries = $this->object->_load_libraries(0, NULL, FALSE);
        if ($libraries && isset($libraries['default']) && $libraries['default']) {

            // Create a factory to hatch C_Lightbox_Library objects
            $factory = $this->object->_get_registry()->get_utility('I_Component_Factory');

            // Get the name of the default library
            $library_name = $libraries['default'];

            // Create the library object
            $retval = $this->object->_hatch_lightbox_library(
                $factory, $library_name, $libraries[$library_name]
            );
        }

        return $retval;
    }

    function find_all($order='', $start=FALSE, $limit=FALSE, $context=FALSE)
    {
        $retval = array();

        // Load libraries from options table
        $libraries = $this->object->_load_libraries();

        // Create a factory to hatch C_Lightbox_Library objects
        $factory = $this->object->_get_registry()->get_utility('I_Component_Factory');

        // Iterate through the results and hatch new objects
        foreach ($libraries as $library_name => $properties) {
            $retval[] = $this->object->_hatch_lightbox_library(
                $factory, $library_name, $properties
            );
        }

        return $retval;

    }

    function find_first($where=FALSE, $vars=array(), $order='', $context=FALSE)
    {
        $retval = NULL;
        $libraries = $this->object->_load_libraries();
        if ($libraries) {

            // Create a factory to hatch C_Lightbox_Library objects
            $factory = $this->object->_get_registry()->get_utility('I_Component_Factory');

            foreach ($libraries as $library_name => $properties) {
                $retval = $this->object->_hatch_lightbox_library(
                    $factory, $library_name, $properties
                );
                break;
            }
        }

        return $retval;
    }
}


//class C_Lightbox_Library extends C_Active_Record
class C_Lightbox_Library extends C_Component
{
    var $factory_method = 'lightbox_library';

    function define()
    {
        parent::define();
        $this->remove_mixin('Mixin_Active_Record_Query');
        $this->add_mixin('Mixin_Lightbox_Library');
        $this->implement('I_Lightbox_Library');
    }

    function initialize($properties=array(), $context=FALSE)
    {
        parent::initialize($properties, $context);
        $this->table_name = $this->db->get_table_name('options');
        $this->id_field = 'option_id';
    }

    function validation()
    {
        $this->validates_presence_of('name');
        $this->validates_presence_of('html');
    }
}
