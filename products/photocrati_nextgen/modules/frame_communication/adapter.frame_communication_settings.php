<?php

class A_Frame_Communication_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('frame_communication_option_name', 'X-Frame-Events');
	}
}