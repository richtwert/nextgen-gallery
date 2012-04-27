<?php

class Mixin_Attached_Gallery_Validation extends Mixin
{
    function validation()
    {
        $this->object->validates_presence_of('post_id');
        $this->object->validates_presence_of('gallery_type');
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
		$gallery_mapper = $this->object->_get_registry()->get_utility('I_Gallery_Mapper');
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
	 * Gets images for the attached gallery
	 * @param array $conditions filter the number of images returned
	 * @param boolean $return_mapper
	 */
	function get_images($conditions=array(), $return_mapper=FALSE)
	{
		$retval = array();

		if ($this->object->images) {
			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
			$key = $mapper->get_primary_key_col();
			$where = $key.' IN '.implode(',', $this->object->images);
			$mapper->select()->where($where);
			if ($conditions) $mapper->where($conditions);
			$retval = $return_mapper ? $mapper : $mapper->run_query();
		}

		return $retval;
	}


	/**
	 * Gets the first image of the attached gallery
	 * @return stdObject|stdClass
	 */
	function get_first_image()
	{
		$retval = NULL;

		if ($this->object->images) {
			$mapper = $this->object->_get_registry()->get_utility('I_Gallery_Image_Mapper');
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

class C_Attached_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Attached_Gallery_Mapper';


	function define()
	{
		$this->add_mixin('Mixin_Attached_Gallery_Validation');
		$this->add_mixin('Mixin_Attached_Gallery_Methods');
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

		// Create an empty images array if need be
		if (empty($this->images)) $this->reset_images();
	}
}
