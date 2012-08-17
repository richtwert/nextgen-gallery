<?php

class Mixin_Attached_Gallery_Validation extends Mixin
{
    function validation()
    {
        $this->object->validates_presence_of('post_id');
        $this->object->validates_presence_of('gallery_type');
		$this->object->Validates_presence_of('gallery_source');

		// When the source for images is an existing gallery,
		// then we require images to be selected
		if (isset($this->object->source) &&
		in_array($this->object->source, array('existing_gallery'))) {
			$this->object->validates_presence_of(
				'images',
				array(),
				"No images selected. You must at least choose one image to display."
			);

			// If there are images selected, then we must validate them as well
			if ($images) foreach ($images as $image) {
				// TODO: Validate images
			}
		}
    }
}


class Mixin_Attached_Gallery_Methods extends Mixin
{
	/**
	 * Gets the associated gallery
	 * @return C_NextGen_Gallery
	 */
   function get_gallery()
    {
		$gallery_mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
		return $gallery_mapper->find($this->object->gallery_id);
    }

	/**
	 * Gets the gallery type associated with the attached gallery
	 * @return array
	 */
    function get_gallery_type()
    {
        return C_Gallery_Type_Registry::get($this->object->gallery_type);
    }


	/**
	 * Gets the css class associated with the gallery type
	 * @return type
	 */
    function get_gallery_type_css_class()
    {
        return strtolower(
            preg_replace(
                "/[^A-Za-z0-9]+/",
                '_',
                $this->object->gallery_type
            )
        );
    }


	/**
	 * Gets the first image of the attached gallery
	 * @return stdObject|stdClass
	 */
	function get_first_image()
	{
		$retval = NULL;

		if ($this->object->images) {
			$mapper = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
			$retval = $mapper->find(array_shift($image->object->images));
		}

		return $retval;
	}

	/**
	 * Determines if an image is included or not in the attached gallery
	 * @param int $image_id
	 * @return boolean
	 */
	function is_image_included($image_id)
	{
		return in_array($image_id, $this->images);
	}

	/**
	 * Adds an image to the attached gallery
	 * @param type $image_id
	 */
	function add_image($image_id)
	{
		$this->object->images[] = $image_id;
	}

	/**
	 * Resets (clears) the images included in the attached gallery
	 */
	function reset_images()
	{
		$this->object->images = array();
	}
}

/**
 * Creates a model representing a gallery attached to a post/page
 */
class C_Attached_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Attached_Gallery_Mapper';

	/**
	 * Define the object
	 */
	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_Attached_Gallery_Validation');
		$this->add_mixin('Mixin_Attached_Gallery_Methods');
		$this->implement('I_Attached_Gallery');
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
			$mapper = $this->get_registry()->get_utility($this->_mapper_interface);
		}
		parent::initialize($mapper, $properties, $context);

		// Create an empty images array if need be
		if (empty($this->images)) $this->reset_images();
	}
}
