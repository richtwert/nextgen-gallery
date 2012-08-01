<?php

include_once('class.test_ngglegacy_gallerystorage_driver_base.php');

/**
 * Tests the gallery storage component with the NggLegacy Driver, using
 * the Custom Post driver for the datamapper
 */
class C_Test_NggLegacy_GalleryStorage_With_CustomPosts extends C_Test_NggLegacy_GalleryStorage_Driver_Base
{
	function __construct($label='Gallery Storage Test, using NggLegacy Driver + DataMapper with CustomPost Driver')
	{
		parent::__construct($label, 'custom_post_datamapper');
	}
}