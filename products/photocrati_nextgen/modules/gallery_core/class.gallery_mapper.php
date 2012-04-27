<?php

class C_Gallery_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_gallery', array('gallery', $context));
		$this->set_model_factory_method('gallery');
		$this->implement('I_Gallery_Mapper');
	}

	function initialize()
	{
		$this->_post_title_field = 'title';
	}
}