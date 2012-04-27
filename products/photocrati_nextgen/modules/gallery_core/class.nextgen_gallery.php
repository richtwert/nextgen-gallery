<?php

/**
 * We create this as an extension as it encapsulates methods which will most
 * likely be replaced by adapters.
 *
 * For other than that, we could have just defined these methods in C_NextGen_Gallery
 */
class Hook_NextGen_Gallery_Persistence extends Hook
{
	/**
	 * Before validation, sets the gallery path
	 */
	function set_gallery_path()
	{
        // Here for legacy purposes
        $this->object->name = apply_filters('ngg_gallery_name', $this->object->name);

        // Get the default gallery storage path
        $name = $this->object->name;
        $pc_options = $this->object->_get_registry()->get_singleton_utility('I_Photocrati_Options');
        $storage_dir = $pc_options->storage_dir;
        unset($pc_options);
        $gallery_dir = path_join(ABSPATH, path_join($storage_dir, $name));

        // Check for existing folder
        if ( is_dir($gallery_dir) ) {
            $suffix = 1;
            do {
                    $alt_name = substr ($name, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "_$suffix";
                    $gallery_dir = path_join(ABSPATH, path_join($storage_dir,$alt_name));
                    $dir_check = is_dir($gallery_dir);
                    $suffix++;
            } while ( $dir_check );
            $name = $alt_name;
        }

        // Set gallery dir
        $this->object->path = path_join($storage_dir, $name);
	}

	/**
	 * Once a gallery has been created, NextGEN legacy fires an action for
	 * other plugins to use.
	 */
	function fire_wordpress_action()
	{
		// here you can inject a custom function. Again for legacy purposes
        do_action('ngg_created_new_gallery', $this->object->id());
	}

    /**
     * Returns the absolute path to the gallery path
     */
    function get_gallery_path()
    {
        return path_join(ABSPATH, $this->object->path);
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
		$this->add_post_hook('validate', 'Add Gallery Path', 'Hook_NextGen_Gallery_Persistence', 'set_gallery_path');
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

    /**
     * Validates whether the gallery can be saved
     */
    function validation()
    {
        $this->name = sanitize_file_name( sanitize_title($this->title));
        $this->slug = nggdb::get_unique_slug( sanitize_title($this->title), 'gallery' );

        // If author is missing, then set to the current user id
        // TODO: Using wordpress function. Should use abstraction
        if (!$this->author) {
            $this->author = get_current_user_id();
        }

        $this->validates_presence_of('title');
		$this->validates_presence_of('name');
        $this->validates_uniqueness_of('slug');
        $this->validates_numericality_of('author');
    }
}
