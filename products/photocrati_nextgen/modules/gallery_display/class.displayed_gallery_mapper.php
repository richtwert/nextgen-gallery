<?php

class C_Displayed_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
	static $_instances = array();

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