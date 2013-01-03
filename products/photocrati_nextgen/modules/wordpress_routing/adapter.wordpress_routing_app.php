<?php

class A_WordPress_Routing_App extends Mixin
{
	/**
	 * Ensures that the parameters in the request uri don't interfere with normal WordPress routing

     * @return boolean
	 */
	function passthru()
	{
		$_SERVER['REQUEST_URI'] = trailingslashit(
            $this->object->strip_param_segments(
			    $this->object->get_router()->get_request_uri()
		    )
        );

		return TRUE;
	}
}