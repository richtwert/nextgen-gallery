<?php

class C_Displayed_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define(NULL, array($context, 'display_gallery'));
		$this->implement('I_Displayed_Gallery_Mapper');
		$this->set_model_factory_method('displayed_gallery');
		$this->add_post_hook(
			'save',
			'Propagate thumbnail dimensions',
			'Hook_Propagate_Thumbnail_Dimensions_To_Settings'
		);
	}


	/**
	 * Initializes the mapper
	 * @param string|array|FALSE $context
	 */
	function initialize()
	{
		parent::initialize('displayed_gallery');
	}


	/**
	 * Gets a singleton of the mapper
	 * @param string|array $context
	 * @return C_Displayed_Gallery_Mapper
	 */
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Displayed_Gallery_Mapper($context);
        }
        return self::$_instances[$context];
    }
}