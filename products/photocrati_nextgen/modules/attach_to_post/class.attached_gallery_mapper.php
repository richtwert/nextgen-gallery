<?php

class C_Attached_Gallery_Mapper extends C_CustomPost_DataMapper_Driver
{
	function define($context=FALSE)
	{
		parent::define('attached_gallery', array($context, 'attached_gallery'));
		$this->implement(('I_Attached_Gallery_Mapper'));
	}
}