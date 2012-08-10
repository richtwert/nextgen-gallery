<?php
/***
{
		Module: photocrati-mediarss
}
***/
class M_MediaRss extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-mediarss',
			'MediaRss',
			'Generates MediaRSS feeds of image collections',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		$this->_add_routes();
	}

	/**
	 * Adds a route for MediaRSS feeds
	 */
	function _add_routes()
	{
		$router = $this->_get_registry()->get_utility('I_Router');
		$router->add_route(__CLASS__, 'C_MediaRSS_Controller', array(
            'uri'=>$router->routing_pattern('mediarss')
        ));
	}
}

new M_MediaRss();