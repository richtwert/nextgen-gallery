<?php
/**
 {
	Module: photocrati-backbone,
    Depends: { photocrati-mvc }
 }
 */
class M_BackboneJs extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'photocrati-backbonejs',
			'Backbone JS',
			'Provides utilities for integrating Backbone.js applications',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
			$context
		);
	}
}

new M_BackboneJs();