<?php

class C_Gallery_Image_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_pictures', array('attachment', $context));
	}
}