<?php

class GalleryStorageDriverNotSelectedException extends RuntimeException
{
	function __construct($message='', $code=NULL, $previous=NULL)
	{
		if (!$message) $message = "No gallery storage driver selected.";
		parent::__construct($message, $code, $previous);
	}
}

class Mixin_GalleryStorage extends Mixin
{
	/**
	 * Returns the name of the class which provides the gallerystorage
	 * implementation
	 * @return string
	 */
	function _get_driver_factory_method($context=FALSE)
	{
		$factory_method = '';

		// No constant has been defined to establish a global gallerystorage driver
		if (!defined('GALLERYSTORAGE_DRIVER')) {

			// Get the datamapper configured in the database
			$settings = $this->object->_get_registry()->get_utility('I_NextGen_Settings');
			$factory_method = $settings->gallerystorage_driver;
			if (!$factory_method) throw new GalleryStorageDriverNotSelectedException();

			// Define a constant and use this as the global gallerystorage driver,
			// unless running in a SimpleTest Environment
			if (!isset($GLOBALS['SIMPLE_TEST_RUNNING']))
				define('GALLERYSTORAGE_DRIVER', $factory_method);
		}

		// Use the globally defined gallerystorage driver in the constant
		else $factory_method = GALLERYSTORAGE_DRIVER;

		return $factory_method;
	}
}

class C_Gallery_Storage extends C_Component
{
    public static $_instances = array();

	function define($object_name, $context=FALSE)
	{
		$this->add_mixin('Mixin_GalleryStorage');
		$this->wrap('I_GalleryStorage_Driver', array(&$this, '_get_driver'), array($object_name, $context));
		$this->implement('I_Gallery_Storage');
	}

    static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context]))
        {
            self::$_instances[$context] = new C_Gallery_Storage($context);
        }
        return self::$_instances[$context];
    }

	/**
	 * Returns the implementation for the gallerystorage
	 * @param array $args
	 * @return mixed
	 */
	function _get_driver($args)
	{
		$object_name = $args[0];
		$context = $args[1];
		$factory_method = $this->_get_driver_factory_method($context);
		$factory = $this->_get_registry()->get_utility('I_Component_Factory');
		return $factory->create($factory_method, $object_name, $context);
	}
}
