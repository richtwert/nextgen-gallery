<?php

class C_Album_Mapper extends C_DataMapper
{

	function define($context=FALSE)
	{

		if (!is_array($context)) $context = array($context);
		array_push($context, 'album');
		parent::define('ngg_album', $context);
		$this->get_wrapped_instance()->add_mixin('Mixin_Album_Mapper');
		$this->implement('I_Album_Mapper');
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