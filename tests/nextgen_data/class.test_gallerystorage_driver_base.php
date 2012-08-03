<?php
require_once(path_join(PHOTOCRATI_GALLERY_TESTS_DIR, 'class.test_component_base.php'));

/**
 * Provides a base class for test cases testing C_Components
 */
abstract class C_Test_GalleryStorage_Driver_Base extends C_Test_Component_Base
{

	/**************************************************************************
	 * HELP METHODS
	**************************************************************************/
	function assert_valid_gallerystorage_driver()
	{
		$this->assertTrue($this->storage->implements_interface('I_GalleryStorage_Driver'));
	}
}