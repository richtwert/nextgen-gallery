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
 * - order_by
 * - order_direction
 */
class C_Displayed_Gallery extends C_DataMapper_Model
{
	var $_mapper_interface = 'I_Displayed_Gallery_Mapper';

	function define($mapper=FALSE, $properties=FALSE, $context=FALSE)
	{
		parent::define($mapper, $properties, $context);
		$this->add_mixin('Mixin_Displayed_Gallery_Validation');
		$this->add_mixin('Mixin_Displayed_Gallery_Instance_Methods');
        $this->add_mixin('Mixin_Gallery_Source_Queries');
        $this->add_mixin('Mixin_Album_Source_Queries');
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
			if (count($this->object->container_ids) == 0 && count($this->object->entity_ids) == 0) {
				$this->object->add_error("Additional source criteria required", 'source');
			}
		}

		return $this->object->is_valid();
	}
}

class Mixin_Gallery_Source_Queries extends Mixin
{
   /**
     * Gets the images associated with the displayed gallery
     * @param int $limit
     * @param int $offset
     */
    function get_entities($limit=FALSE, $offset=FALSE, $id_only=FALSE, $skip_exclusions=FALSE)
    {
        $settings           = $this->object->get_registry()->get_utility('I_NextGen_Settings');
        $mapper             = $this->object->get_registry()->get_utility('I_Image_Mapper');
        $gallery_key        = 'galleryid'; // foreign key
        $image_key          = $mapper->get_primary_key_column();
        $run_image_query    = TRUE;
        $retval             = array();

        // Make default field selection
        $mapper->select($id_only ? $image_key : '*');

        // Create query
        switch ($this->object->source) {
            case 'all':
            case 'all_images':
                $mapper = $this->object->_modify_mapper_for_exclusions($mapper);
                break;
            case 'gallery':
            case 'galleries':
                $mapper = $this->object->_create_image_query_for_galleries(
                    $mapper, $image_key, $gallery_key, $settings, $limit, $offset, $id_only, $skip_exclusions
                );
                break;
            case 'recent':
            case 'recent_images':
                $mapper->order_by('imagedate', 'DESC');
                $mapper = $this->object->_modify_mapper_for_exclusions($mapper);
                if ($this->object->container_ids)
                    $mapper->where(array("{$gallery_key} in (%s)", $this->object->container_ids));
                break;
            case 'random':
            case 'random_images':
                $mapper = $this->object->_create_random_image_query(
                    $mapper, $image_key, $gallery_key, $settings, $limit, $offset, $id_only
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
					if (!$image_ids) $run_image_query = FALSE;

					// If entities have been provided, and we're to skip exclusions
					if ($this->object->entity_ids && $skip_exclusions) {
						$mapper->where(
                            array("{$image_key} IN (%s)", $this->object->entity_ids)
                        );
					}

					// If both container_ids and entity_ids are specified, then
					// we need to calculate exclusions...
					elseif ($this->object->entity_ids) {
						// We're going to return entities from all of the tags
						// specified, but mark which images will be excluded. To do
						// so, we have to create a dynamic column
						$select = $id_only ? $image_key : '*';
						$set = implode(',', array_reverse($this->object->entity_ids));
						$select .= ", @row := FIND_IN_SET({$image_key}, '{$set}') AS sortorder";
						$select .= ", IF(@row = 0, 1, 0) AS exclude";
						$mapper->select($select);

						// Limit to the images using the appropriate tags
						$mapper->where(
                            array("{$image_key} IN (%s)", $image_ids)
                        );

						// A user might want to sort the results by the order of
						// images that they specified to be included. For that,
						// we need some trickery by reversing the order direction
						$order_direction = $this->object->order_direction == 'ASC' ? 'DESC' : 'ASC';
						$mapper->order_by($this->object->order_by, $order_direction);

						// When using a custom order (sortorder), we should apply a
						// secondary sort order to maintain the default sort order
						// for galleries as much as possible
						if ($this->object->order_by == 'sortorder') {
							$settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
							if ($settings->galSort != 'sortorder') {
								$mapper->order_by($settings->galSort, $settings->galSortDir);
							}
						}
					}

					// Only containers were specified. Simply where clause
					else {
						$mapper->where(
                            array("{$image_key} IN (%s)", $image_ids)
                        );
					}
                }
                break;
            case 'albums':
            case 'album':
                $run_image_query = FALSE;
                $retval = $this->object->_create_album_query($limit, $offset, $id_only, $skip_exclusions);
                break;
            default:
                $run_image_query = FALSE;
                break;
        }
        if ($run_image_query) {

            // Apply a limit to the number of images retrieved
            if (!$limit) {
                $limit = $settings->gallery_display_limit;
            }
            $mapper->limit($limit, $offset);
            $retval = $mapper->run_query();
        }

        return $retval;
    }


    /**
     * Gets only included images
     * @param int $limit
     * @param int $offset
     * @param bool $id_only
     */
    function get_included_entities($limit=FALSE, $offset=FALSE, $id_only=FALSE)
    {
        return $this->object->get_entities($limit, $offset, $id_only, TRUE);
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
    function _create_random_image_query($mapper, $image_key, $gallery_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE)
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
                array("{$gallery_key} in (%s)", $this->object->container_ids)
            );
        }

        return $mapper;
    }

    /**
     * Modifies a datamapper to handle image inclusion/exclusion based on the return parameter
     *
     * @param C_Image_Mapper $mapper
     * @return C_Image_Mapper
     */
    function _modify_mapper_for_exclusions($mapper)
    {
        $image_key = $mapper->get_primary_key_column();
        if (empty($this->object->returns))
            $this->object->returns = 'included';

        if ('both' == $this->object->returns)
        {
            $mapper->where(array("{$image_key} IN (%s)", $this->object->exclusions, $this->object->entity_ids));
        }
        else {
            if (!empty($this->object->exclusions) && 'included' == $this->object->returns)
                $mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));
            if (!empty($this->object->exclusions) && 'excluded' == $this->object->returns)
                $mapper->where(array("{$image_key} IN (%s)", $this->object->exclusions));
            if (!empty($this->object->entity_ids) && 'included' == $this->object->returns)
                $mapper->where(array("{$image_key} IN %s", $this->object->entity_ids));
            if (!empty($this->object->entity_ids) && 'excluded' == $this->object->returns)
                $mapper->where(array("{$image_key} NOT IN %s", $this->object->entity_ids));
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
    function _create_image_query_for_galleries($mapper, $image_key, $gallery_key, $settings, $limit=FALSE, $offset=FALSE, $id_only=FALSE, $skip_exclusions=FALSE)
    {
        // We can do that by specifying what gallery ids we
        // want images from:
        if ($this->object->container_ids && !$this->object->entity_ids) {
            $mapper->where(array("{$gallery_key} IN (%s)", $this->object->container_ids));

            // the return param demands we be able to switch; we must be able to return the normal list of included
            // entities but also be able to return only the items left out
            if ($this->object->exclusions && 'included' == $this->object->returns)
                $mapper->where(array("{$image_key} NOT IN (%s)", $this->object->exclusions));
            if ($this->object->exclusions && 'excluded' == $this->object->returns)
                $mapper->where(array("{$image_key} IN (%s)", $this->object->exclusions));

            // Apply sorting
            $mapper->order_by($this->object->order_by, $this->object->order_direction);
        }

        // Finally, a user can specify what galleries they're interested in, and select what images in particular
        // they want. Exclusions are then calculated rather than specified. NOTE: This is used in the Attach to Post
        // interface
        elseif ($this->object->container_ids && $this->object->entity_ids) {

            // We're going to return images from all of the galleries
            // specified, but mark which images will be excluded. To do
            // so, we have to create a dynamic column
            $select = $id_only ? $image_key : '*';
            $set = implode(',', array_reverse($this->object->entity_ids));
            $select .= ", @row := FIND_IN_SET({$image_key}, '{$set}') AS `sortorder`";
            $select .= ", IF(@row = 0, @exclude := 1, @exclude := 0) AS `exclude`";
            $mapper->select($select);

            $mapper = $this->object->_modify_mapper_for_exclusions($mapper);

            // Limit by specified galleries
            $mapper->where(array("{$gallery_key} IN (%s)", $this->object->container_ids));

            // A user might want to sort the results by the order of
            // images that they specified to be included. For that,
            // we need some trickery by reversing the order direction
			$order_direction = $this->object->order_direction == 'ASC' ? 'DESC' : 'ASC';
            $mapper->order_by($this->object->order_by, $order_direction);

            // When using a custom order (sortorder), we should apply a
            // secondary sort order to maintain the default sort order
            // for galleries as much as possible
            if ($this->object->order_by == 'sortorder')
            {
                $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');
				if ($settings->galSort != 'sortorder')
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
            'recent_images',
            'recent',
            'random_images',
            'random',
            'tags',
            'image_tags'
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
     * Gets the number of images to display
     * @param int|FALSE $limit
     * @param int|FALSE $offset
     * @return int
     */
    function get_entity_count($limit=FALSE, $offset=FALSE)
    {
        $result = $this->object->get_entities($limit, $offset, TRUE);
        if ($result) $result = count($result);
        else $result = 0;
        return $result;
    }
}


class Mixin_Album_Source_Queries extends Mixin
{
    /**
     * Gets gallery entities to be displayed by in the displayed gallery (when album is the source)
     * @param int $limit
     * @param int $offset
     * @param bool $id_only
     */
    function _create_album_query($limit=FALSE, $offset=FALSE, $ids_only=FALSE, $skip_exclusions=FALSE)
    {
        $gallery_mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
        $gallery_key    = $gallery_mapper->get_primary_key_column();
        $album_mapper   = $this->object->get_registry()->get_utility('I_Album_Mapper');
        $album_key      = $album_mapper->get_primary_key_column();
        $settings       = $this->object->get_registry()->get_utility('I_NextGen_Settings');
        $retval         = array();

        // Apply default limit
        if (!$limit)
            $limit = $settings->gallery_display_limit;

        if (!$offset)
            $offset = 0;

        // Assume where the entities are coming from
        $entity_ids = $this->object->entity_ids;

        // If container ids have been specified, then get their entity ids
        if ($this->object->container_ids)
        {
            $entity_ids = $this->object->_get_album_entities(
                $album_mapper, $album_key, $this->object->container_ids, $ids_only
            );
        }

        // Collect gallery ids and sub-album ids
        $gallery_ids  = array();
        $galleries    = array();
        $subalbum_ids = array();
        $subalbums    = array();

        foreach ($entity_ids as $id) {
            if (strpos($id, 'a') === 0)
                $subalbum_ids[] = intval(str_replace('a', '', $id));
            else
                $gallery_ids[] = intval($id);
        }

        // If we're not to return ids, then get the galleries and albums to display
        if (!$ids_only)
        {
            if ($gallery_ids)
                $galleries = $gallery_mapper->select('*')
                                            ->where(array("{$gallery_key} IN (%s)", $gallery_ids))
                                            ->run_query();
            if ($subalbum_ids)
                $subalbums = $album_mapper->select('*')
                                          ->where(array("{$album_key} IN (%s)", $subalbum_ids))
                                          ->run_query();

            // Get image totals for galleries
            $img_mapper = $this->object->get_registry()->get_utility('I_Image_Mapper');
            $img_mapper->select('COUNT(*) AS "count", galleryid')
                       ->where(array("galleryid IN (%s)", $gallery_ids))
                       ->group_by('galleryid');
            $img_totals = $img_mapper->run_query();

            // Return entities in specified order
            foreach ($entity_ids as $id) {
                $obj = NULL;

                // Is the object an album? If so,
                // make it look like a gallery
                if (strpos($id, 'a') === 0)
                {
                    $obj           = array_shift($subalbums);
                    $obj->galdesc  = $obj->albumdesc;
                    $obj->title    = $obj->name;
                    $obj->is_album = TRUE;
                    $obj->counter  = 0;
                }
                // The object is a gallery. Get the image count
                else {
                    $obj           = array_shift($galleries);
                    $img_total     = array_shift($img_totals);
                    $obj->counter  = (int)$img_total->count;
                    $obj->is_album = FALSE;
                }

                // If we failed to get an object, we'll assume that users forgot to prefix
                // the album id with 'a'.
                if ($obj)
                {
                    // Determine whether the object is excluded
                    if (in_array($id, $this->object->exclusions))
                    {
                        $obj->exclude = 1;
                    }
                    elseif ($this->object->entity_ids) {
                        if (in_array($id, $this->object->entity_ids))
                        {
                            $obj->exclude = 0;
                        }
                        else {
                            $obj->exclude = 1;
                        }
                    }
                    else {
                        $obj->exclude = 0;
                    }

                    // Return the object, if it's not to be excluded and we're to skip exclusions
                    if (!($skip_exclusions && $obj->exclude == 1))
                        $retval[] = $obj;
                }
            }

			// Are we to sort?
			if ($this->object->order_by == 'sortorder')
                $this->object->order_by = NULL;

			if ($this->object->order_by)
				usort($retval, array(&$this, 'sort_album_result'));
        }
        // Return just the entity ids
        else {
            if ($skip_exclusions)
                $retval = array_diff($entity_ids, $this->object->exclusions);
            else
                $retval = $entity_ids;
        }

        // Apply limit and offset
        return array_slice($retval, $offset, $limit);
    }


	/**
	 * Sorts the results of an album query
	 * @param stdClass $a
	 * @param stdClass $b
	 */
	function sort_album_result($a, $b)
	{
		$key = $this->object->order_by;
		return strcmp($a->$key, $b->$key);
	}


    /**
     * Gets all entities from a list of albums
     * @param C_Album_Mapper $album_mapper
     * @param string $album_key
     * @param array $album_ids
     * @param bool $ids_only
     * @param bool $skip_subalbums
     * @return array
     */
    function _get_album_entities($album_mapper, $album_key, $album_ids=array(), $ids_only=FALSE, $skip_subalbums=FALSE)
    {
        $retval = array();
        $album_mapper->select($ids_only ? $album_key.', sortorder' : '*')->where(array("{$album_key} IN (%s)", $album_ids));
        $albums = $album_mapper->run_query();
        $entities = array();
        foreach ($albums as $album) {
			if (is_array($album->sortorder)) {
				foreach ($album->sortorder as $entity_id) $entities[] = $entity_id;
				if ($skip_subalbums) foreach ($entities as $entity_id) {
					if (strpos($entity_id, 'a') === FALSE) $retval[] = $entity_id;
				}
				else $retval = $entities;
			}
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
        $album_sources = array(
            'album',
            'albums',
            'recent_galleries',
            'random_galleries',
            'gallery_tags'
        );

        if (in_array($this->object->source, $album_sources)) {
            $mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
            $album_key = $mapper->get_primary_key_column();
            $mapper->select()->where(array("{$album_key} IN (%s)", $this->object->container_ids));
            $retval =  $mapper->run_query();
        }
        return $retval;
    }
}

/**
 * Provides instance methods useful for working with the C_Displayed_Gallery
 * model
 */
class Mixin_Displayed_Gallery_Instance_Methods extends Mixin
{
	/**
	 * Gets the display type object used in this displayed gallery
	 * @return C_Display_Type
	 */
	function get_display_type()
	{
		$mapper = $this->object->get_registry()->get_utility('I_Display_Type_Mapper');
		return  $mapper->find_by_name($this->object->display_type, TRUE);
	}


    /**
     * Returns a transient for the displayed gallery
     * @return string
     */
    function to_transient()
    {
        $transient_handler = $this->object->get_registry()->get_utility('I_Transients');
        $key = md5(serialize($this->object->get_entity()));
        $transient_handler->set_value($key, $this->object->get_entity());
        return $key;
    }


    /**
     * Applies the values of a transient to this object
     * @param string $transient_id
     */
    function apply_transient($transient_id)
    {
        $transient_handler = $this->object->get_registry()->get_utility('I_Transients');
        $transient = $transient_handler->get_value($transient_id);
        if ($transient) {
            $this->object->_stdObject = $transient;
        }
    }
}
