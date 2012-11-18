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
		$this->add_mixin('Mixin_Displayed_Gallery_Queries');
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
		// Valid sources
		$this->object->validates_presence_of('source');

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

			// Is the display type compatible with the source? E.g., if we're
			// using a display type that expects images, we can't be feeding it
			// galleries and albums
			if (($source = $this->get_source())) {
				if (!$display_type->is_compatible_with_source($source)) {
					$this->object->add_error(
						_('Source not compatible with selected display type'),
						'display_type'
					);
				}
			}

		}
		else {
			$this->object->add_error('Invalid display type', 'display_type');
		}

		return $this->object->is_valid();
	}
}

class Mixin_Displayed_Gallery_Queries extends Mixin
{
	function get_entities($limit=FALSE, $offset=FALSE, $id_only=FALSE, $returns='included')
	{
		$retval = array();

		// Ensure that all parameters have values that are expected
		if ($this->object->_parse_parameters()) {

			// Is this an image query?
			$source_obj = get_source();
			if (in_array('image', $source_obj->returns)) {
				$retval = $this->object->_get_images_entities($source_obj, $limit, $offset, $id_only, $returns);
			}

			// Is this a gallery/album query?
			elseif (in_array('gallery', $source_obj->returns)) {
				$retval = $this->object->_get_album_and_gallery_entities($source_obj, $limit, $offset, $id_only, $returns);
			}
		}

		return $retval;
	}

	/**
	 * Gets all images in the displayed gallery
	 * @param C_Displayed_Gallery_Source $source_obj
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $id_only
	 * @param string $returns
	 */
	function _get_image_entities($source_obj, $limit, $offset, $id_only, $returns)
	{
		$image_mapper	= $this->get_registry()->get_utility('I_Image_Mapper');
		$image_key		= $image_mapper->get_primary_key_column();
		$select			= $ids_only ? $image_key : '*';
		$sort_direction	= $this->object->order_direction;
		$sort_by		= $this->object->order_by;

		// We start with the most difficult query. When returns is "both", we
		// need to return a list of both included and excluded entity ids, and
		// mark specifically which entities are excluded
		if ($returns == 'both') {

			// We need to add two dynamic columns, one called "sortorder" and
			// the other called "exclude". They're self explanation
			$set = implode(",", array_reverse($this->object->entity_ids));
			$select .= ", @row := FIND_IN_SET({$image_key}, '{$set}') AS sortorder";
			$select .= ", IF(@row = 0, 1, 0) AS exclude";
			$mapper->select($select);

			// A user might want to sort the results by the order of
			// images that they specified to be included. For that,
			// we need some trickery by reversing the order direction
			$sort_direction = $this->object->order_direction == 'ASC' ? 'DESC' : 'ASC';
		}

		// When returns is "included", the query is relatively simple. We
		// just provide a where clause to limit how many images we're returning
		// based on the entity_ids, exclusions, and container_ids parameters
		if ($returns == 'included') {
			$mapper->select($select);

			// Filter based on entity_ids selection
			if ($this->object->entity_ids) {
				$mapper->where(array("{$image_key} IN %s", $this->object->entity_ids));
			}

			// Filter based on exclusions selection
			if ($this->object->exclusions) {
				$mapper->where(array("{$image_key} NOT IN %s", $this->object->exclusions));
			}
		}

		// When returns is "excluded", it's a little more complicated as the
		// query is the negated form of the "included". entity_ids become the
		// list of exclusions, and exclusions become the list of entity_ids to
		// return. All results we return must be marked as excluded
		elseif ($returns == 'excluded') {
			// Mark each result as excluded
			$select .= ", 1 AS exclude";
			$mapper->select($select);

			// Is this case, entity_ids become the exclusions
			$exclusions = $this->object->entity_ids;

			// Remove the exclusions always takes precedence over entity_ids, so
			// we adjust the list of ids
			if ($this->object->exclusions) foreach ($this->object->exclusions as $excluded_entity_id) {
				if (($index = array_search($excluded_entity_id, $exclusions)) !== FALSE) {
					unset($exclusions[$index]);
				}
			}

			// Filter based on exclusions selection
			if ($exclusions) {
				$mapper->where(array("{$image_key} NOT IN %s", $exclusions));
			}

			// Filter based on selected exclusions
			else if ($this->object->exclusions) {
				$mapper->where(array("{$image_key} IN %s", $this->object->exclusions));
			}
		}

		// Filter based on containers_ids. Container ids is a little more
		// complicated as it can contain gallery ids or tags
		if ($this->object->container_ids) {

			// Container ids are tags
			if ($source_obj->container_type == 'tag') {
				$term_ids = $this->object->_get_term_ids_for_tags($this->object->container_ids);
				$mapper->where(array("{$image_key} IN %s",get_objects_in_term($term_ids, 'ngg_tag')));
			}

			// Container ids are gallery ids
			else {
				$mapper->where(array("galleryid IN %s", $this->object->container_ids));
			}
		}

		// Adjust the query more based on what source was selected
		if ($this->object->source == 'recent_images') {
			$sort_direction = 'DESC';
			$sort_by = 'imagedate';
		}
		elseif ($this->object->source == 'random_images') {
			$sort_by = 'rand()';
		}

		// Apply a sorting order
		$mapper->order_by($sort_by, $sort_direction);

		// Apply a limit
		if ($limit) {
			if ($offset) $mapper->limit($limit, $offset);
			else		 $mapper->limit($limit);
		}

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

	/**
	 * Gets all gallery and album entities from albums specified, if any
	 * @param C_Displayed_Gallery_Source $source_obj
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $id_only
	 * @param array $returns
	 */
	function _get_album_and_gallery_entities($source_obj, $limit=FALSE, $offset=FALSE, $id_only=FALSE, $returns='included')
	{
		// Albums queries and difficult and inefficient to perform due to the
		// database schema. To complicate things, we're returning two different
		// types of entities - galleries, and sub-albums.
		// The user prefixes entity_id's with an 'a' to distinguish album ids
		// from gallery ids. E.g. entity_ids=[1, "a2", 3]
		$album_mapper	= $this->get_registry()->get_utility('I_Album_Mapper');
		$album_key		= $album_mapper->get_primary_key_column();
		$gallery_mapper	= $this->get_registry()->get_utility('I_Gallery_Mapper');
		$gallery_key	= $gallery_mapper->get_primary_key_column();
		$select			= $id_only ? $album_key : '*';
		$retval		= array();

		// If no exclusions are specified, are entity_ids are specified,
		// and we're to return is "included", then we have a relatively easy
		// query to perform - we just fetch each entity listed in
		// the entity_ids field
		if ($returns == 'included' && $this->object->entity_ids && empty($this->object->exclusions)) {
			$retval = $this->object->_entities_to_galleries_and_albums(
				$this->object->entity_ids, $id_only
			);
		}

		// It's not going to be easy. We'll start by fetching the albums
		// and retrieving each of their entities
		else {
			// Start the query
			$album_mapper->select($select);

			// Filter by container ids
			if ($this->object->container_ids) {
				$album_mapper->where(
					array("${$album_key} IN %s", $this->object->container_ids)
				);
			}

			// Fetch the albums, and find the entity ids of the sub-albums
			// and galleries
			$entity_ids		= array();
			$included_ids	= array();
			$excluded_ids	= array();
			foreach ($album_mapper->run_query() as $album) {
				$entity_ids = array_merge($entity_ids, implode(",", $album->sortorder));
			}

			// Break the list of entities into two groups, included entities
			// and excluded entity ids
			// --
			// If a specific list of entity ids have been specified, then
			// we know what entity ids are meant to be included. We can compute
			// the intersect and also determine what entity ids are to be
			// excluded
			if ($this->object->entity_ids) {

				// Determine the real list of included entity ids. Exclusions
				// always take precedence
				$included_ids = $this->object->entity_ids;
				foreach ($this->object->exclusions as $excluded_id) {
					if (($index = array_search($excluded_id, $included_entity_ids)) !== FALSE) {
						unset($included_entity_ids[$index]);
					}
				}
				$excluded_ids = array_diff($entity_ids, $included_ids);
			}

			// We only have a list of exclusions.
			elseif ($this->object->exclusions) {
				$included_ids = array_diff($entity_ids, $this->object->exclusions);
				$excluded_ids = array_diff($entity_ids, $included_ids);
			}

			// We've built our two groups. Let's determine how we'll focus on
			// them
			// --
			// We're interested in only the included ids
			if ($returns == 'included')
				$retval = $this->object->_entities_to_galleries_and_albums($included_ids, $id_only);

			// We're interested in only the excluded ids
			elseif ($returns == 'excluded')
				$retval = $this->object->_entities_to_galleries_and_albums($excluded_ids, $id_only, $excluded_ids);

			// We're interested in both groups
			else {
				$retval = $this->object->_entities_to_galleries_and_albums($entity_ids, $id_only, $excluded_ids);
			}
		}

		// Sort the entities
		if ($this->object->order_by && $this->object->order_by != 'sortorder')
			usort($retval, array(&$this, '_sort_album_result'));

		// Limit the entities
		if ($limit && $offset)
			$retval = array_slice($retval, $offset, $limit);

		return $retval;
	}


	/**
	 * Returns the total number of entities in this displayed gallery
	 * @param string $returns
	 * @returns int
	 */
	function get_entity_count($returns='included')
	{
		// Is this an image query?
		$source_obj = get_source();
		if (in_array('image', $source_obj->returns)) {
			return count($this->object->_get_image_entities($source_obj, FALSE, FALSE, TRUE, $returns));
		}

		// Is this a gallery/album query?
		elseif (in_array('gallery', $source_obj->returns)) {
			return count($this->object->_get_album_and_gallery_entities($source_obj, FALSE, FALSE, TRUE, $returns));
		}
	}

	/**
	 * Returns all included entities for the displayed gallery
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $id_only
	 * @return array
	 */
	function get_included_entities($limit=FALSE, $offset=FALSE, $id_only=FALSE)
	{
		return $this->object->get_entities($limit, $offset, $id_only, 'included');
	}

	/**
	 * Returns a list of valid source names, paired with the name of the
	 * underlying true source name
	 * @return array
	 */
	function _get_source_map()
	{
		$sources = array();
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Source_Mapper');
		foreach ($mapper->find_all() as $entity) {
			$sources[$entity->name] = $entity->name;
			foreach ($entity->aliases as $alias) $sources[$alias] = $entity->name;
		}
		return $sources;
	}

	/**
	 * Parses the list of parameters provided in the displayed gallery, and
	 * ensures everything meets expectations
	 * @return boolean
	 */
	function _parse_parameters()
	{
		$valid = FALSE;

		// Ensure that the source is valid
		$sources = $this->object->_get_source_map();
		if (isset($sources[$this->object->source])) {
			$this->object->source = $sources[$this->object->source];
			$valid = TRUE;
		}

		return $valid;
	}

	/**
	 * Returns a list of term ids for the list of tags
	 * @global wpdb $wpdb
	 * @param array $tags
	 * @return array
	 */
	function _get_term_ids_for_tags($tags)
	{
		global $wpdb;

		// Convert container ids to a string suitable for WHERE IN
		$container_ids = array();
		foreach ($tags as $container) {
			$container_ids[]= "'{$container}'";
		}
		$container_ids = implode(',', $container_ids);

		// Get all term_ids for each image tag slug
		$term_ids = array();
		$query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug IN ({$container_ids}) ORDER BY term_id ASC ");
		foreach ($wpdb->get_results($query) as $row) {
			$term_ids[] = $row->term_id;
		}

		return $term_ids;
	}

	/**
	 * Takes a list of entities, and returns the mapped
	 * galleries and sub-albums
	 * @param array $entity_ids
	 * @return array
	 */
	function _entities_to_galleries_and_albums($entity_ids, $id_only=FALSE, $exclusions=array())
	{
		$retval			= array();
		$gallery_ids	= array();
		$album_ids		= array();
		$album_mapper	= $this->get_registry()->get_utility('I_Album_Mapper');
		$album_key		= $album_mapper->get_primary_key_column();
		$gallery_mapper	= $this->get_registry()->get_utility('I_Gallery_Mapper');
		$gallery_key	= $gallery_mapper->get_primary_key_column();
		$album_select	= $id_only ? $album_key : '*';
		$gallery_select = $id_only ? $gallery_key : '*';

		// Segment entity ids into two groups - galleries and albums
		foreach ($entity_ids as $entity_id) {
			if (substr($entity_id, 0, 1) == 'a')
				$album_ids[]	= intval(substr($entity_id, 1));
			else
				$gallery_ids[]	= intval($entity_id);
		}

		// Adjust query to include an exclude property
		if ($exclusions) {
			$set = implode(",", array_reverse($exclusions));
			$album_select	.= ", @row := FIND_IN_SET({$album_key}, '{$set}')";
			$album_select	.= ", IF(@row = 0, 1, 0) AS exclude";
			$gallery_select	.= ", @row := FIND_IN_SET({$gallery_key}, '{$set}')";
			$gallery_select	.= ", IF(@row = 0, 1, 0) AS exclude";
		}

		// Fetch entities
		$galleries	= $gallery_mapper->select($gallery_select)->where(
			array("{$gallery_key} IN %s", $gallery_ids)
		);
		$albums		= $album_mapper->select($album_select)->where(
			array("{$album_key} IN %s", $album_ids)
		);

		// Reorder entities according to order specified in entity_ids
		foreach ($entity_ids as $entity_id) {
			if (substr($entity_id, 0, 1) == 'a')
				$retval[] = array_shift($albums);
			else
				$retval[] = array_shift($galleries);
		}

		return $retval;
	}

	/**
	 * Sorts the results of an album query
	 * @param stdClass $a
	 * @param stdClass $b
	 */
	function _sort_album_result($a, $b)
	{
		$key = $this->object->order_by;
		return strcmp($a->$key, $b->$key);
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
	 * Gets the corresponding source instance
	 * @return C_Displayed_Gallery_Source
	 */
	function get_source()
	{
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Source_Mapper');
		return $mapper->find($this->object->source, TRUE);
	}

	/**
	 * Returns the galleries queries in this displayed gallery
	 * @return array
	 */
	function get_galleries()
	{
		$retval = array();
		if (($source = $this->object->get_source())) {
			if (in_array('image', $source->returns)) {
				$mapper			= $this->object->get_registry()->get_utility('I_Gallery_Mapper');
				$gallery_key	= $mapper->get_primary_key_column();
				$mapper->select();
				if ($this->object->container_ids) {
					$mapper->where(array("{$gallery_key} IN %s", $this->object->container_ids));
				}
				$retval			= $mapper->run_query();
			}
		}
		return $retval;
	}

	/**
	 * Gets albums queried in this displayed gallery
	 * @return array
	 */
	function get_albums()
	{
		$retval = array();
		if (($source = $this->object->get_source())) {
			if (in_array('album', $source->returns)) {
				$mapper		= $this->get_registry()->get_utility('I_Album_Mapper');
				$album_key	= $mapper->get_primary_key_column();
				if ($this->object->container_ids) {
					$mapper->where(array("{$album_key} IN %s", $this->object->container_ids));
				}
				$retval		= $mapper->run_query();
			}
		}
		return $retval;
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
