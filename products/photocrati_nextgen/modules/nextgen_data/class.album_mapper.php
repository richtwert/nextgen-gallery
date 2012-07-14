<?php

class C_Album_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_album', array('album', $context));
		$this->set_model_factory_method('album');
		$this->implement('I_Album_Mapper');
	}


	function initialize($context)
	{
		parent::initialize(array('album', $context));
		$this->_post_title_field = 'name';
	}
}