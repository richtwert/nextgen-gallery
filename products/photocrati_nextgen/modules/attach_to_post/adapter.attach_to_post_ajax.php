<?php

/**
 * Provides AJAX actions for the Attach To Post interface
 * TODO: Need to add authorization checks to each action
 */
class A_Attach_To_Post_Ajax extends Mixin
{
	var $attach_to_post = NULL;

	/**
	 * Retrieves the attach to post controller
	 */
	function initialize()
	{
		$this->attach_to_post = $this->object->get_registry()->get_utility('I_Attach_To_Post_Controller');
	}


	/**
	 * Returns a list of image sources for the Attach to Post interface
	 * @return type
	 */
	function get_attach_to_post_sources_action()
	{
		$response = array();
		$response['sources'] = $this->attach_to_post->get_sources();
		return $response;
	}


	/**
	 * Gets existing galleries
	 * @return array
	 */
	function get_existing_galleries_action()
	{
		$response = array();

		$limit = $this->object->param('limit');
		$offset = $this->object->param('offset');

		// We return the total # of galleries, so that the client can make
		// pagination requests
		$mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
		$response['total'] = $mapper->count();
		$response['limit'] = $limit = $limit ? $limit : 0;
		$response['offset'] = $offset = $offset ? $offset : 0;

		// Get the galleries
		$mapper->select();
		if ($limit) $mapper->limit($limit, $offset);
		$response['galleries'] = $mapper->run_query();

		return $response;
	}


    /**
     * Gets existing albums
     * @return array
     */
    function get_existing_albums_action()
    {
        $response = array();

        $limit  = $this->object->param('limit');
        $offset = $this->object->param('offset');

        // We return the total # of albums, so that the client can make pagination requests
        $mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
        $response['total'] = $mapper->count();
        $response['limit'] = $limit = $limit ? $limit : 0;
        $response['offset']= $offset = $offset ? $offset : 0;

        // Get the albums
        $mapper->select();
        if ($limit) $mapper->limit($limit, $offset);
        $response['albums'] = $mapper->run_query();

        return $response;
    }

	/**
	 * Gets existing image tags
	 * @return array
	 */
	function get_image_tags_action()
	{
		$response = array();

		$limit = $this->object->param('limit');
		$offset = $this->object->param('offset');
		$response['limit'] = $limit = $limit ? $limit : 0;
		$response['offset'] = $offset = $offset ? $offset : 0;
		$response['image_tags'] = array();
		$params = array(
			'number'	=>	$limit,
			'offset'	=>	$offset,
			'fields'	=>	'names'
		);
		foreach (get_terms('ngg_tag', $params) as $term) {
			$response['image_tags'][] = array(
				'id'	=>	$term,
				'title'	=>	$term
			);
		}
		$response['total'] = count(get_terms('ngg_tag', array('fields' => 'ids')));

		return $response;
	}

	/**
	 * Gets entities (such as images) for a displayed gallery (attached gallery)
	 */
	function get_displayed_gallery_entities_action()
	{
		$response = array();
		if (($params = $this->object->param('displayed_gallery'))) {
			$limit	 = $this->object->param('limit');
			$offset  = $this->object->param('offset');
			$factory = $this->object->get_registry()->get_utility('I_Component_Factory');
			$displayed_gallery = $factory->create('displayed_gallery');
			foreach ($params as $key => $value) $displayed_gallery->$key = $value;
			$response['limit']	= $limit = $limit ? $limit : 0;
			$response['offset'] = $offset = $offset ? $offset : 0;
			$response['count']	= $displayed_gallery->get_entity_count();
			$response['entities'] = $displayed_gallery->get_entities($limit,$offset);
			$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');
			foreach ( $response['entities'] as &$entity) {
                $image = $entity;
                if (in_array($displayed_gallery->source, array('album','albums'))) {
                    $image = $entity->previewpic;
                    if ($entity->is_album) {
                        $id = $entity->{$entity->id_field};
                        $entity->{$entity->id_field} = 'a'.$id;
                    }
                }
                $entity->thumb_url	=	$storage->get_thumb_url($image);
                $entity->thumb_size	=	$storage->get_thumb_dimensions($image);
				if (!$entity->thumb_size) $entity->thumb_size = array(
					'width' => '', 'height' => ''
				);
			}
		}
		else {
			$response['error'] = _('Missing parameters');
		}
		return $response;
	}


	/**
	 * Saves the displayed gallery
	 */
	function save_displayed_gallery_action()
	{
		$response = array();
		$mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

		// Do we have fields to work with?
		if (($params = $this->object->param('displayed_gallery'))) {

			// Existing displayed gallery ?
			if (($id = $this->object->param('id'))) {
				$displayed_gallery = $mapper->find($id, TRUE);
				if ($displayed_gallery) {
					foreach ($params as $key => $value) $displayed_gallery->$key = $value;
				}
			}
			else {
				$factory = $this->object->get_registry()->get_utility('I_Component_Factory');
				$displayed_gallery = $factory->create('displayed_gallery', $mapper, $params);
			}

			// Save the changes
			if ($displayed_gallery) {
				if ($displayed_gallery->save()) $response['displayed_gallery'] = $displayed_gallery->get_entity();
				else $response['validation_errors'] = $this->attach_to_post->show_errors_for($displayed_gallery, TRUE);
			}
			else
			{
				$response['error'] = _('Displayed gallery does not exist');
			}
		}
		else $response['error'] = _('Invalid request');

		return $response;
	}
}