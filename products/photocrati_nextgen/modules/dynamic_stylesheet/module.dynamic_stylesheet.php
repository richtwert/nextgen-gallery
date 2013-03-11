<?php

/*
{
	Module: photocrati-dynamic_stylesheet,
	Depends: { photocrati-mvc, photocrati-lzw }
}
 */
class M_Dynamic_Stylesheet extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'photocrati-dynamic_stylesheet',
			'Dynamic Stylesheet',
			'Provides the ability to generate and enqueue a dynamic stylesheet',
			'0.2',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
			$context
		);
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility(
			"I_Dynamic_Stylesheet", 'C_Dynamic_Stylesheet_Controller'
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Router', 'A_Dynamic_Stylesheet_Routes'
		);
		$this->get_registry()->add_adapter(
			'I_Settings_Manager', 'A_Dynamic_Stylesheet_Settings'
		);
	}
}

new M_Dynamic_Stylesheet;