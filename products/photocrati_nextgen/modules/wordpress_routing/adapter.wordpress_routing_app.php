<?php

class A_WordPress_Routing_App extends Mixin
{
	function passthru()
	{
		$_SERVER['REQUEST_URI'] = $this->object->strip_param_segments(
			$this->object->get_router()->get_request_uri()
		);

		return TRUE;
	}
}