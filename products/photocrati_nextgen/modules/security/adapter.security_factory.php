<?php

class A_Security_Factory extends Mixin
{
	function wordpress_security_manager($context=FALSE)
	{
		return new C_WordPress_Security_Manager($context);
	}

	function security_manager($context=FALSE)
	{
		return $this->object->wordpress_security_manager($context);
	}
}
