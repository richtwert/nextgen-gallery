<?php

class C_Test_NggLegacy_GalleryStorage_Driver extends C_Test_GalleryStorage_Driver_Base
{
	function test_gallerystorage_constructor()
	{
		$this->storage = $this->get_factory()->create('ngglegacy_gallery_storage');
		$this->assertEqual(get_class($this->storage), 'C_NggLegacy_GalleryStorage_Driver');
		$this->assert_valid_gallerystorage_driver();
	}

	function test_get_upload_path()
	{
		xdebug_start_trace();
		$upload_path = $this->storage->get_upload_path();
		xdebug_stop_trace();
		$this->assertTrue(is_string($upload_path));
	}
}

?>
