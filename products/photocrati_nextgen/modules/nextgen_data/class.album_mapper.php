<?php

class C_Album_Mapper extends C_DataMapper
{
	function define($context=FALSE)
	{
		parent::define('ngg_album', array('album', $context));
		$this->get_wrapped_instance()->add_mixin('Mixin_Album_Mapper');
		$this->implement('I_Album_Mapper');
	}


	function initialize($context)
	{
		parent::initialize(array('album', $context));
		$this->set_model_factory_method('album');
	}
}

/**
 * Sets the post title to the name of the album
 */
class Mixin_Album_Mapper extends Mixin
{
	function get_post_title($entity)
	{
		return $entity->name;
	}
}