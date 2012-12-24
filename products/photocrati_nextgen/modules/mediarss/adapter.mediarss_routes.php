<?php

class A_MediaRSS_Routes extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'serve_request',
			'Adds MediaRSS routes',
			get_class(),
			'add_mediarss_routes'
		);
	}

	function add_mediarss_routes()
	{
		$app = $this->get_registry()->get_utility('I_Router')->create_app('/mediarss');
        $app->route(
            '/',
            array(
                'controller' => 'C_MediaRSS_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );
	}
}