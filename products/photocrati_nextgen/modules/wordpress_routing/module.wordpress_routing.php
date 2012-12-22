<?php

/***
 {
	Module: photocrati-wordpress_routing,
	Depends: { photocrati-mvc }
 }
 ***/
class M_WordPress_Routing extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-wordpress_routing',
			'WordPress Routing',
			"Integrates the MVC module's routing implementation with WordPress",
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}
}

new M_WordPress_Routing();