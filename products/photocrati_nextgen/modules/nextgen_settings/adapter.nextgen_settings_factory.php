<?php

class A_NextGen_Settings_Factory extends Mixin
{
	function lightbox_library($mapper=FALSE, $properties=array(), $context=FALSE)
	{
		return new C_Lightbox_Library($mapper, $properties, $context);
	}
}