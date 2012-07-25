<?php

/**
 * Model for NextGen Gallery Images
 */
class C_NextGen_Gallery_Image extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Gallery_Image_Mapper';

    function define()
    {
        parent::define();
        $this->implement('I_Gallery_Image');
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

		// Initialize
		parent::initialize($mapper, $properties, $context);
	}

    function validate()
    {
        $this->validates_presence_of('galleryid');
        $this->validates_numericality_of('galleryid');
        $this->validates_numericality_of($this->id());
    }

	/**
	 * Returns the model representing the gallery associated with this image
	 * @return C_NextGen_Gallery|stdClass
	 */
    function get_gallery($model=FALSE)
    {
		$gallery_mapper = $this->_get_registry()->get_utility('I_Gallery_Mapper');
        return $gallery_mapper->find($this->galleryid, $model);
    }
}