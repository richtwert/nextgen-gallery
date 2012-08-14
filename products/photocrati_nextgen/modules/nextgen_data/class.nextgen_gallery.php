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
		$this->object->set_defaults();

		// Set what will be the path to the gallery
		$storage = $this->object->_get_registry()->get_utility('I_Gallery_Storage');
		$this->object->path = $storage->get_upload_relpath($this->object);
		unset($storage);

        $this->object->validates_presence_of('title');
		$this->object->validates_presence_of('name');
        $this->object->validates_uniqueness_of('slug');
        $this->object->validates_numericality_of('author');

		return $this->object->is_valid();
    }

	/**
	 * Sets default values for the gallery
	 */
	function set_defaults()
	{
		// If author is missing, then set to the current user id
        // TODO: Using wordpress function. Should use abstraction
        if (!$this->object->author) {
            $this->object->author = get_current_user_id();
        }

		// Generate name and slug based off of the title
		if (isset($this->object->title)) {
			$this->object->name = sanitize_file_name( sanitize_title($this->object->title));
			$this->object->name = apply_filters('ngg_gallery_name', $this->object->name);
			$this->object->slug = nggdb::get_unique_slug( sanitize_title($this->object->title), 'gallery' );
		}
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
    function define()
    {
        parent::define();
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
	function initialize($properties = FALSE, $mapper=FALSE, $context = FALSE) {

		// Get the mapper is not specified
		if (!$mapper) {
			$mapper = $this->_get_registry()->get_utility($this->_mapper_interface);
		}
		parent::initialize($mapper, $properties, $context);
	}
}
