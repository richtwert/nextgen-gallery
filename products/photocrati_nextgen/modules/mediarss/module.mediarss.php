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

        $this->get_registry()
             ->get_utility('I_Router')
             ->add_pre_hook('serve_request', 'Adds MediaRSS routes', 'Hook_MediaRSS_Routes', 'add_routes');
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
	}

}

new M_MediaRss();