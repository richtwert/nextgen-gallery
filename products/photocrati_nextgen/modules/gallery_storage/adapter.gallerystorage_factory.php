<?php

class A_GalleryStorage_Factory extends Mixin
{
	function ngglegacy_gallery_storage($context=FALSE)
	{
		return new C_NggLegacy_GalleryStorage_Driver($context);
	}

	function wordpress_gallery_storage($context=FALSE)
	{
		return new C_WordPress_GalleryStorage_Driver($context);
	}
}