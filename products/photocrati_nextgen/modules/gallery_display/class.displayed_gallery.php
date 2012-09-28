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
			$this->object->validates_presence_of('container_ids');
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
    function get_images($limit=FALSE, $offset=FALSE, $id_only=FALSE, $skip_exclusions=FALSE)
    {
        $settings       = $this->object->get_registry()->get_utility('I_NextGen_Settings');
        $mapper         = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');
        $gallery_key    = 'galleryid'; // foreign key
        $image_key      = $mapper->get_primary_key_column();
        $run_query      = TRUE;

        // Make default field selection
        $mapper->select($id_only ? $image_key : '*');

        // Create query
        switch ($this->object->source) {
            case 'gallery':
            case 'galleries':
                $mapper = $this->object->_create_image_query_for_galleries(
                    $mapper, $image_key, $gallery_key, $settings, $limit, $offset, $id_only, $skip_exclusions
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
                        array("{$gallery_key} in (%s)", $this->object->container_ids)
                    );
                }
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
            $mapper->where(
                array("{$gallery_key} in (%s)", $this->object->container_ids)
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
                array("{$image_key} in %s", $this->object->entity_ids)
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
                array("{$gallery_key} in (%s)", $this->object->container_ids)
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
    function get_image_count($limit=FALSE, $offset=FALSE)
    {
        $result = $this->object->get_images($limit, $offset, TRUE);
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
    function get_album_entities($limit=FALSE, $offset=FALSE, $ids_only=FALSE, $skip_exclusions=FALSE)
    {
        $gallery_mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
        $gallery_key    = $gallery_mapper->get_primary_key_column();
        $album_mapper   = $this->object->get_registry()->get_utility('I_Album_Mapper');
        $album_key      = $album_mapper->get_primary_key_column();
        $settings       = $this->object->get_registry()->get_utility('I_NextGen_Settings');
        $retval         = array();

        // Apply default limit
        if (!$limit) $limit = $settings->gallery_display_limit;

        switch ($this->object->source) {

            case 'albums':
            case 'album':
                $entity_ids = $this->object->entity_ids;

                // If container ids have been specified, then get their entity ids
                if ($this->object->container_ids) {
                    $entity_ids = $this->object->_get_album_entities(
                        $album_mapper, $album_key, $this->object->container_ids, $ids_only
                    );
                }

                // Collect gallery ids and sub-album ids
                $gallery_ids    = array();
                $galleries      = array();
                $subalbum_ids   = array();
                $subalbums      = array();
                foreach ($entity_ids as $id) {
                    if (strpos($id, 'a') === 0) $subalbum_ids[] = intval(str_replace('a', '', $id));
                    else $gallery_ids[] = intval($id);
                }

                // If we're not to return ids, then get the galleries and albums to display
                if (!$ids_only) {
                    if ($gallery_ids) $galleries = $gallery_mapper->select('*')->where(array(
                        "{$gallery_key} IN (%s)", $gallery_ids
                    ))->run_query();
                    if ($subalbum_ids) $subalbums = $album_mapper->select('*')->where(array(
                        "{$album_key} IN (%s)", $subalbum_ids
                    ))->run_query();

                    // Return entities in specified order
                    foreach ($entity_ids as $id) {
                        $obj = NULL;

                        // Get object, whether it be a gallery or sub-album
                        if (strpos($id, 'a') === 0) $obj = array_shift($subalbums);
                        else $obj = array_shift($galleries);

                        // If we failed to get an object, we'll assume that users forgot to prefix
                        // the album id with 'a'.
                        if ($obj) {
                            // Determine whether the object is excluded
                            if (in_array($id, $this->object->exclusions)) $obj->exclude = 1;
                            elseif ($this->object->entity_ids) {
                                if (in_array($id, $this->object->entity_ids)) $obj->exclude = 0;
                                else $obj->exclude = 1;
                            }
                            else $obj->exclude = 0;

                            // Return the object, if it's not to be excluded and we're to skip exclusions
                            if (!($skip_exclusions && $exclude == 1)) $retval[] = $obj;
                        }
                    }
                }

                // Return just the entity ids
                else {
                    if ($skip_exclusions) $retval = array_diff($entity_ids, $this->object->exclusions);
                    else $retval = $entity_ids;
                }

                // Apply limit and offset
                $retval = array_slice($retval, $offset, $limit);
                break;

            // Fetch recent galleries
            case 'recent_galleries':
                // TODO Not finished - shouldn't be used
                $gallery_mapper->select($ids_only ? $gallery_key : '*')->order_by($gallery_key, 'DESC')->limit($limit, $offset);
                if ($this->object->container_ids) {
                    $gallery_mapper->where(array("{$gallery_key} IN (%s)", $this->object->_get_album_entities(
                        $album_mapper,
                        $album_key,
                        $this->object->container_ids,
                        $ids_only,
                        TRUE
                    )));
                }
                break;

            // Fetch random galleries
            case 'random_galleries':
                // TODO Not finished - shouldn't be used
                $gallery_mapper->select($ids_only ? $gallery_key : '*')->order_by('rand()')->limit($limit, $offset);
                $gallery_mapper->where(array("{$gallery_key} NOT IN (%s)", $this->object->exclusions));
                if ($this->object->container_ids) {
                    $gallery_mapper->where(array("{$gallery_key} IN (%s)", $this->object->_get_album_entities(
                        $album_mapper,
                        $album_key,
                        $this->object->container_ids,
                        $ids_only,
                        TRUE
                    )));
                }
                break;

            // Get a list of galleries using specific tags
            case 'gallery_tags':
                // TODO Not finished - shouldn't be used
                break;
        }
        return $retval;
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
        $album_mapper->select($ids_only ? $album_key : '*')->where(array("{$album_key} IN (%s)", $album_ids));
        $albums = $album_mapper->run_query();
        $entities = array();
        foreach ($albums as $album) foreach ($album->sortorder as $entity_id) $entities[] = $entity_id;
        if ($skip_subalbums) foreach ($entities as $entity_id) {
            if (strpos($entity_id, 'a') === FALSE) $retval[] = $entity_id;
        }
        else $retval = $entities;
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
    function get_album_entity_count($limit=FALSE, $offset=FALSE)
    {
        $result = $this->object->get_album_entities($limit, $offset, TRUE);
        if ($result) $result = count($result);
        else $result = 0;
        return $result;
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
}
