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
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_MediaRss_Routes');
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility(
			'I_MediaRSS_Controller', 'C_MediaRSS_Controller'
		);
	}

}

new M_MediaRss();