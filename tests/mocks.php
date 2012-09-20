<?php

if (!class_exists('Mock_Mixin_DataMapper_Driver')) {
	class Mock_Mixin_DataMapper_Driver extends Mixin
	{
		function convert_to_model($stdObject, $context=FALSE)
		{
			return new C_DataMapper_Model($this->object, $stdObject, $context);
		}
	}
}

if (!class_exists('Mock_Mixin_DataMapper_Model_Validations')) {
	class Mock_Mixin_DataMapper_Model_Validations extends Mixin
	{
		function validation()
		{
			$this->object->validates_presence_of('post_title');
			$this->object->validates_length_of('post_title', 3, '>');
		}
	}
}
