<?php

class C_Gallery_Image_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_pictures', array('attachment', $context));
		$this->set_model_factory_method('gallery_image');
	}
}