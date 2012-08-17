<?php

/**
 * Associates a Display Type with a collection of images
 *
 * * Properties:
 * - source				(gallery, album, recent_images, random_images, etc)
 * - container_ids		(gallery ids, album ids, tag ids, etc)
 * - display_type		(name of the display type being used)
 * - display_settings	(settings for the display type)
 * - exclusions			(excluded entity ids)
 * - entity_ids			(specific images/galleries to include, sorted)
 */
class C_Displayed_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Displayed_Gallery_Mapper';

	function define($mapper=FALSE, $properties=FALSE, $context=FALSE)
	{
		parent::define($mapper, $properties, $context);
		$this->add_mixin('Mixin_Displayed_Gallery_Validation');
		$this->add_mixin('Mixin_Displayed_Gallery_Instance_Methods');
		$this->implement('I_Displayed_Gallery');
	}


	/**
	 * Initializes a display type with properties
	 * @param FALSE|C_Displayed_Gallery_Mapper $mapper
	 * @param array|stdClass|C_Displayed_Gallery $properties
	 * @param FALSE|string|array $context
	 */
	function initialize($mapper=FALSE, $properties=array())
	{
		if (!$mapper) $mapper = $this->get_registry()->get_utility($this->_mapper_interface);
		parent::initialize($mapper, $properties);
	}
}

/**
 * Provides validation
 */
class Mixin_Displayed_Gallery_Validation extends Mixin
{
	function set_defaults()
	{
		// If the display type is set, then get it's settings and apply them as
		// defaults to the "display_settings" of the displayed gallery
		if (isset($this->object->display_type)) {

			// Get display type mapper
			$display_type = $this->object->get_display_type();
			if (!$display_type) {
				$this->object->add_error('Invalid display type', 'display_type');
			}
			else {
				$this->object->display_settings = $this->object->array_merge_assoc(
					$display_type->settings, $this->object->display_settings
				);
			}
		}

		// Default ordering
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		if (!isset($this->object->order_by))
			$this->object->order_by = $settings->galSort;
		if (!isset($this->object->order_direction))
			$this->object->order_direction = $settings->galSortDir;
	}


	function validation()
	{
		$this->object->set_defaults();

		$this->object->validates_presence_of('source');
		$this->object->validates_presence_of('display_type');
		if (in_array($this->object->source, array('galleries', 'albums', 'tags'))) {
			$this->object->validates_presence_of('container_ids');
		}

		// Validate the display settings
		if (($display_type = $this->object->get_display_type())) {
			$display_type->settings = $this->object->display_settings;
			if (!$display_type->validate()) {
				foreach ($display_type->get_errors() as $property => $errors) {
					foreach ($errors as $error) {
						$this->object->add_error($error, $property);
					}
				}
			}
		}

		return $this->object->is_valid();
	}
}

/**
 * Provides instance methods useful for working with the C_Displayed_Gallery
 * model
 */
class Mixin_Displayed_Gallery_Instance_Methods extends Mixin
{
	/**
	 * Gets the images associated with the displayed gallery
	 * @param int $limit
	 * @param int $offset
	 */
	function get_images($limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$mapper = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
		$image_key = $mapper->get_primary_key_column();
		$mapper->select($id_only ? $image_key : '*');
		$run_query = TRUE;

		// Create query
		switch ($this->object->source) {
			case 'gallery':
			case 'galleries':
				$mapper = $this->_create_image_query_for_galleries(
					$mapper, $image_key, $settings, $limit, $offset, $id_only
				);
				break;
			case 'recent':
			case 'recent_images':
				$mapper->order_by('imagedate', 'DESC');

				// Exclude specific images
				if ($this->object->exclusions)
					$mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));

				// Limit by specified galleries
				if ($this->object->container_ids) {
					$mapper->where(
						array("galleryid in (%s)", $this->object->container_ids)
					);
				}
				break;
			case 'random':
			case 'random_images':
				$mapper = $this->_create_random_image_query(
					$mapper, $image_key, $settings, $limit, $offset, $id_only
				);
				break;
			case 'tags':
				$term_ids = $wpdb->get_col( $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug IN ({$this->object->container_ids}) ORDER BY term_id ASC "));
				$image_ids = get_objects_in_term($term_ids, 'ngg_tag');
				$mapper->where(
					array("{$image_key} IN (%s)", $image_ids)
				);
				break;
			default:
				$run_query = FALSE;
				break;
		}
		if ($run_query) {

			// Apply a limit to the number of images retrieved
			if (!$limit) {
				$limit = $settings->gallery_display_limit;
			}
			$mapper->limit($limit, $offset);

			// Return the results
			return $mapper->run_query();
		}
		else return array();
	}

	/**
	 * Creates a datamapper query for finding random images
	 * @param C_Gallery_Image_Mapper $mapper
	 * @param string $image_key
	 * @param C_NextGen_Settings $settings
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @param bool $id_only
	 * @return C_Gallery_Image_Mapper
	 */
	function _create_random_image_query($mapper, $image_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		// We'll get the first and last ID
		$max = 0;
		$min = 0;
		$results = $mapper->select("MAX({$image_key}) AS max_id")->run_query();
		if ($results) $max = intval($results[0]->max_id);
		if (!$max) $max = $mapper->count();
		$results = $mapper->select("MIN({$image_key}) AS min_id")->run_query();
		if ($results) $min = intval($results[0]->min_id);

		// Calculate a random start and end point
		$min = rand($min, $max);
		$max = $min+$settings->gallery_display_limit;

		// Create the query
		$mapper->select($id_only ? $image_key : '*');
		$mapper->where(array("{$image_key} BETWEEN %d AND %d", $min, $max));
		$mapper->order_by('rand()');

		// Exclude specific images
		if ($this->object->exclusions)
			$mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));

		// Limit by specified galleries
		if ($this->object->container_ids) {
			$mapper->where(
				array("galleryid in (%s)", $this->object->container_ids)
			);
		}

		return $mapper;
	}


	/**
	 * Creates a datamapper query for images, using galleries as a source
	 * for images
	 * @param C_Gallery_Image_Mapper $mapper
	 * @param string $image_key
	 * @param C_NextGen_Settings $settings
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @param bool $id_only
	 * @return C_Gallery_Image_Mapper
	 */
	function _create_image_query_for_galleries($mapper, $image_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		// We can do that by specifying what gallery ids we
		// want images from:
		if ($this->object->container_ids && !$this->object->entity_ids) {
			$mapper->where(
				array("galleryid in (%s)", $this->object->container_ids)
			);

			// We can exclude images from those galleries we're not
			// interested in...
			if ($this->object->exclusions) {
				$mapper->where(
					array(
						"{$image_key} NOT IN (%s)",
						$this->object->exclusions
					)
				);
			}

			// Apply sorting
			$mapper->order_by($this->object->order_by, $this->object->order_direction);
		}

		// Or, instead of specifying what galleries we want images from,
		// we can specify the images in particular that we want to fetch
		elseif ($this->object->entity_ids && !$this->object->container_ids) {
			$mapper->where(
				array("pid in %s", $this->object->entity_ids)
			);
			$mapper->order_by($this->object->order_by, $this->object->order_direction);
		}

		// Finally, a user can specify what galleries they're interested
		// in, and select what images in particular they want. Exclusions
		// are then calculated rather than specified.
		// NOTE: This is used in the Attach to Post interface
		elseif ($this->object->entity_ids && $this->object->container_ids) {

			// We're going to return images from all of the galleries
			// specified, but mark which images will be excluded. To do
			// so, we have to create a dynamic column
			$select = $id_only ? $image_key : '*';
			$set = implode(',', array_reverse($this->object->entity_ids));
			$select .= ", @row := FIND_IN_SET({$image_key}, '{$set}') AS sortorder";
			$select .= ", IF(@row = 0, @exclude := 1, @exclude := 0) AS exclude";
			$mapper->select($select);

			// Limit by specified galleries
			$mapper->where(
				array("galleryid in (%s)", $this->object->container_ids)
			);

			// A user might want to sort the results by the order of
			// images that they specified to be included. For that,
			// we need some trickery by reversing the order direction
			if ($this->object->order_by == 'sortorder') {
				if ($this->object->order_direction == 'ASC')
					$this->object->order_direction = 'DESC';
				else
					$this->object->order_direction = 'ASC';
			}
			$mapper->order_by($this->object->order_by, $this->object->order_direction);

			// When using a custom order (sortorder), we should apply a
			// secondary sort order to maintain the default sort order
			// for galleries as much as possible
			if ($this->object->order_by == 'sortorder') {
				$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
				$mapper->order_by($settings->galSort, $settings->galSortDir);
			}
		}

		return $mapper;
	}


	/**
	 * Get the galleries associated with this display
	 */
	function get_galleries()
	{
		$retval = array();
		if ($this->object->source == 'gallery') {
			$mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
			$gallery_key = $mapper->get_primary_key_column();
			$mapper->select()->where(array("{$gallery_key} IN (%s)", $this->object->container_ids));
			return $mapper->run_query();
		}
		return $retval;
	}


	/**
	 * Gets the number of images to display
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @return int
	 */
	function get_image_count($limit=FALSE, $offset=FALSE)
	{
		$result = $this->object->get_images($limit, $offset, TRUE);
		if ($result) $result = count($result);
		else $result = 0;
		return $result;
	}

	/**
	 * Gets the display type object used in this displayed gallery
	 * @return C_Display_Type
	 */
	function get_display_type()
	{
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		return  $mapper->find_by_name($this->object->display_type, TRUE);
	}
}