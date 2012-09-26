<?php

/**
 * Represents a named collection of galleries
 *
 * * Properties:
 * - source				(gallery, album, recent_images, random_images, etc)
 * - container_ids		(gallery ids, album ids, tag ids, etc)
 * - display_type		(name of the display type being used)
 * - display_settings	(settings for the display type)
 * - exclusions			(excluded entity ids)
 * - entity_ids			(specific images/galleries to include, sorted)
 */
class C_Album extends C_DataMapper_Model
{
    var $_mapper_interface = 'I_Album_Mapper';


    function define($mapper=FALSE, $properties=FALSE, $context=FALSE)
    {
        parent::define($mapper, $properties, $context);
        $this->add_mixin('Mixin_NextGen_Album_Instance_Methods');
        $this->implement('I_Album');
    }


    /**
     * Instantiates an Album object
     * @param bool|\C_DataMapper|\FALSE $mapper
     * @param array $properties
     */
    function initialize($mapper=FALSE, $properties=array()) {

        // Get the mapper is not specified
        if (!$mapper) {
            $mapper = $this->get_registry()->get_utility($this->_mapper_interface);
        }

        // Initialize
        parent::initialize($mapper, $properties);
    }
}

/**
 * Provides instance methods for the album
 */
class Mixin_NextGen_Album_Instance_Methods extends Mixin
{
    function validate()
    {
        $this->validates_presence_of('name');
        return $this->object->is_valid();
    }

    /**
     * Gets all galleries associated with the album
     */
    function get_galleries($models=FALSE)
    {
        $retval = array();
        $mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
        $gallery_key = $mapper->get_primary_key_column();
        $retval = $mapper->find_all(array("{$gallery_key} IN %s", $this->object->sortorder), $models);
        return $retval;
    }
}