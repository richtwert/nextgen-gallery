<?php

/**
 * This hook is triggered to fire a WordPress action after the NextGen Gallery
 * been successfully saved
 */
class Hook_NextGen_Gallery_Persistence extends Hook
{
	/**
	 * Once a gallery has been created, NextGEN legacy fires an action for
	 * other plugins to use.
	 */
	function fire_wordpress_action()
	{
		$retval = $this->object->get_method_property(
			'save', ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// here you can inject a custom function. Again for legacy purposes
		if ($retval) do_action('ngg_created_new_gallery', $this->object->id());
	}
}


class Mixin_NextGen_Gallery_Validation
{
    /**
     * Validates whether the gallery can be saved
     */
    function validation()
    {
		// Set what will be the path to the gallery
		$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');
		$this->object->path = $storage->get_upload_relpath($this->object);
		unset($storage);

        $this->object->validates_presence_of('title');
		$this->object->validates_presence_of('name');
        $this->object->validates_uniqueness_of('slug');
        $this->object->validates_numericality_of('author');

		return $this->object->is_valid();
    }
}

/**
 * Creates a model representing a NextGEN Gallery object
 */
class C_NextGen_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Gallery_Mapper';

    /**
     * Defines the interfaces and methods (through extensions and hooks)
     * that this class provides
     */
    function define($properties, $mapper, $context=FALSE)
    {
        parent::define($mapper, $properties, $context);
		$this->add_mixin('Mixin_NextGen_Gallery_Validation');
		$this->add_post_hook('save', 'Fire WordPress Action', 'Hook_NextGen_Gallery_Persistence', 'fire_wordpress_action');
        $this->implement('I_Gallery');
    }

	/**
	 * Instantiates a new model
	 * @param array|stdClass $properties
	 * @param C_DataMapper $mapper
	 * @param string $context
	 */
	function initialize($properties = FALSE, $mapper=FALSE) {

		// Get the mapper is not specified
		if (!$mapper) {
			$mapper = $this->get_registry()->get_utility($this->_mapper_interface);
		}
		parent::initialize($mapper, $properties);
	}
}
