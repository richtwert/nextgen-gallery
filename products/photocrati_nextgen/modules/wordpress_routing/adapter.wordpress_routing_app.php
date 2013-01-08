<?php

class A_WordPress_Routing_App extends Mixin
{
	function initialize()
	{
		// Both set_parameter_value() and remove_parameter() are methods used
		// to generate urls. When they used to generate url for a post
		// on the front-page, the link must to be the post/page itself. We use
		// the 'Hook_WordPress_Include_Post' to achieve that
        $this->add_post_hook(
            'set_parameter_value',
            'Make Wordpress specific URI adjustments',
            'Hook_WordPress_Include_Post'
        );
	}


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