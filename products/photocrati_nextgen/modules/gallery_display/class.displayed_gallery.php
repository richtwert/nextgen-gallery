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
	function validation()
	{
		// Valid display type?
		$this->object->validates_presence_of('display_type');
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
		else {
			$this->object->add_error('Invalid display type', 'display_type');
		}

		// Valid sources
		$this->object->validates_presence_of('source');
		if (in_array($this->object->source, array('galleries', 'albums', 'tags'))) {
			$this->object->validates_presence_of('container_ids');
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
	function get_images($limit=FALSE, $offset=FALSE, $id_only=FALSE, $skip_exclusions=FALSE)
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
				$mapper = $this->object->_create_image_query_for_galleries(
					$mapper, $image_key, $settings, $limit, $offset, $id_only, $skip_exclusions
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
				$mapper = $this->object->_create_random_image_query(
					$mapper, $image_key, $settings, $limit, $offset, $id_only
				);
				break;
			case 'image_tags':
			case 'tags':
                // Continue if we have container ids
				if (($container_ids = $this->object->container_ids)) {

					// Convert container ids to a string suitable for WHERE IN
					// clause
					foreach ($container_ids as &$container) {
						$container = "'{$container}'";
					}
					$container_ids = implode(',', $container_ids);

					// Get all term_ids for each image tag slug
					global $wpdb;
					$term_ids = array();
					$query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug IN ({$container_ids}) ORDER BY term_id ASC ");
					foreach ($wpdb->get_results($query) as $row) {
						$term_ids[] = $row->term_id;
					}

					// Get all images using the provided image tags
					$image_ids = get_objects_in_term($term_ids, 'ngg_tag');
					if (empty($image_ids)) $run_query = FALSE;
					else {
						$mapper->where(
							array("{$image_key} IN (%s)", $image_ids)
						);
					}
				}
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
     * Gets gallery entities to be displayed by in the displayed gallery (when album is the source)
     * @param int $limit
     * @param int $offset
     * @param bool $id_only
     */
    function get_galleries($limit=FALSE, $offset=FALSE, $ids_only=FALSE)
    {
        $mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
        $gallery_key = $mapper->get_primary_key_column();
        $retval = array();

        switch ($this->object->source) {

            // TODO: The below is VERY inefficient due to the way ngglegacy stores album information
            // in the database. We need to divide albums into the following entities:
            // 1) Albums (previewpic, name, description)
            // 2) Galleries (already exist)
            // 3) Album-Galleries (album_id, gallery_id)
            case 'albums':
            case 'album':
                // Fetch galleries for each container (album) specified
                if ($this->object->container_ids && !$this->object->entity_ids) {

                    // Fetch all albums specified and get the gallery ids
                    $gallery_ids = array();
                    $album_mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
                    $album_key = $album_mapper->get_primary_key_column();
                    $albums = $album_mapper->find_all(array("{$album_key} IN %s", $this->object->container_ids));
                    foreach ($albums as $album) $gallery_ids = array_merge($gallery_ids, $album->sortorder);

                    // Create query to fetch galleries
                    $mapper->select($ids_only ? $gallery_key : '*')->where(array("{$gallery_key} IN %s", $gallery_ids));

                    // Sort the galleries
                    $mapper->order_by($this->object->order_by, $this->object->order_direction);

                    // Apply a limit to the number of galleries retrieved
                    if (!$limit) {
                        $limit = $settings->gallery_display_limit;
                    }
                    $mapper->limit($limit, $offset);

                    $retval = $mapper->run_query();
                }
                else {

                }
            case 'recent_galleries':
            case 'random_galleries':
            case 'gallery_tags':
        }
        return $retval;
    }

	/**
	 * Gets only included images
	 * @param int $limit
	 * @param int $offset
	 * @param bool $id_only
	 */
	function get_included_images($limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		return $this->object->get_images($limit, $offset, $id_only, TRUE);
	}

	/**
	 * Creates a datamapper query for finding random images
	 * @param C_Image_Mapper $mapper
	 * @param string $image_key
	 * @param C_NextGen_Settings $settings
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @param bool $id_only
	 * @return C_Image_Mapper
	 */
	function _create_random_image_query($mapper, $image_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		// Create the query
		$mapper->select($id_only ? $image_key : '*');
		$mapper->order_by('rand()');
        $mapper->limit($limit, $offset);

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
	 * @param C_Image_Mapper $mapper
	 * @param string $image_key
	 * @param C_NextGen_Settings $settings
	 * @param int|FALSE $limit
	 * @param int|FALSE $offset
	 * @param bool $id_only
	 * @return C_Image_Mapper
	 */
	function _create_image_query_for_galleries($mapper, $image_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE, $skip_exclusions=FALSE)
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
		elseif ($this->object->entity_ids && (!$this->object->container_ids OR $skip_exclusions)) {
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
	 * Get the galleries (when used as containers, not entities) associated with this
     * displayed gallery
     * @return array
	 */
	function get_gallery_containers()
	{
		$retval = array();
        $gallery_sources = array(
            'gallery',
            'galleries',
            'recent_galleries',
            'random_galleries',
            'gallery_tags'
        );
        if (in_array($this->object->source, $gallery_sources)) {
			$mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
			$gallery_key = $mapper->get_primary_key_column();
			$mapper->select()->where(array("{$gallery_key} IN (%s)", $this->object->container_ids));
			$retval =  $mapper->run_query();
		}
		return $retval;
	}


    /**
     * Gets the albums associated with this displayed gallery
     * @return array
     */
    function get_album_containers()
	{
		$retval = array();
		if ($this->object->source == 'album') {
			$mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
            $album_key = $mapper->get_primary_key_column();
            $mapper->select()->where(array("{$album_key} IN (%s)", $this->object->contianer_ids));
            $retval =  $mapper->run_query();
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
