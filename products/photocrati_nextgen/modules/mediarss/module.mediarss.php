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
        $this->_add_routes();
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
	}

	/**
	 * Adds a route for MediaRSS feeds
	 */
	function _add_routes()
	{
        $router  = $this->get_registry()->get_utility('I_Router');
        $app = $router->create_app();
        $app->route(
            array('/mediarss'),
            array(
                'controller' => 'C_MediaRSS_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );
        $router->add_app($app);
	}
}

new M_MediaRss();