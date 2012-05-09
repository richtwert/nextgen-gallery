<?php

if (!class_exists('Mock_DataMapper_Driver')) {
	class Mock_Mixin_DataMapper_Driver extends Mixin
	{
		function convert_to_model($stdObject, $context=FALSE)
		{
			return new C_DataMapper_Model($this->object, $stdObject, $context);
		}
	}
}
?>
