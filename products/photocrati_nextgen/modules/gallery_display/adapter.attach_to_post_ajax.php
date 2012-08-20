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
}