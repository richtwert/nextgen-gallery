<?php


class Mixin_NextGen_Gallery_Image_Validation extends Mixin
{
	function validate()
	{
		$this->object->set_defaults();

		$this->validates_presence_of('galleryid', 'filename', 'alttext', 'exclude', 'sortorder');
        $this->validates_numericality_of('galleryid');
        $this->validates_numericality_of($this->id());
		$this->validates_numericality_of('sortorder');

		return $this->object->is_valid();
	}

	function set_defaults()
	{
		// If not set already, we'll add an exclude property. This is used
		// by NextGEN Gallery itself, as well as the Attach to Post module
		if (!isset($this->object->exclude)) $exclude = FALSE;

		// If not set already, set a default sortorder
		if (!isset($this->object->sortorder)) $this->object->sortorder = 0;

		// If a filename is set, and no alttext is set, then set the alttext
		// to the basename of the filename (legacy behavior)
		if ($this->object->filename && !isset($this->object->alttext)) {
			$path_parts = pathinfo( $this->object->filename);
			$this->object->alttext = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
		}
	}
}

/**
 * Model for NextGen Gallery Images
 */
class C_NextGen_Gallery_Image extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Gallery_Image_Mapper';

    function define()
    {
        parent::define();
		$this->add_mixin('Mixin_NextGen_Gallery_Image_Validation');
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