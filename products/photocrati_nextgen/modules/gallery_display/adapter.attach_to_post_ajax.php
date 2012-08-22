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
	 * Gets images for a displayed gallery (attached gallery)
	 */
	function get_displayed_gallery_images_action()
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
			$response['count']	= $displayed_gallery->get_image_count();
			$response['images'] = array();
			$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');
			foreach ($displayed_gallery->get_images($limit,$offset) as $image) {
				$image->thumb_url	=	$storage->get_thumb_url($image);
				$image->thumb_size	=	$storage->get_thumb_dimensions($image);
				$response['images']	[]= $image;
			}
		}
		else {
			$response['error'] = _('Missing parameters');
		}
		return $response;
	}
}