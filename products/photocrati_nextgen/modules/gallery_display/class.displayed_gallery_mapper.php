<?php

class C_Displayed_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
	function define()
	{
		parent::define();
		{
			parent::define();
			$this->implement('I_Displayed_Gallery_Mapper');
			$this->set_model_factory_method('display_type');
		}
	}


	/**
	 * Initializes the mapper
	 * @param string|array|FALSE $context
	 */
	function initialize($context = FALSE)
	{
		parent::initialize('displayed_gallery', array($context, 'display_gallery'));
	}
}