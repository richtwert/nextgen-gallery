<?php

/***
	{
		Module: photocrati-mvc
	}
***/

define('MVC_MODULE_DIR', dirname(__FILE__));
define('MVC_TEMPLATE_DIR', path_join(MVC_MODULE_DIR, 'templates'));
require_once(path_join(MVC_MODULE_DIR, 'template_helper.php'));

/**
 * Indicates that a clean exit occurred. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
	class E_Clean_Exit extends RuntimeException
	{

	}
}


class M_MVC extends C_Base_Module
{
	var $rethrow = FALSE;

    function define()
    {
        parent::define(
            "photocrati-mvc",
            "MVC Framework",
            "Provides an MVC architecture for the plugin to use",
            "0.1",
            "http://www.photocrati.com",
            "Photocrati Media",
            "http://www.photocrati.com"
        );
    }


    function initialize()
    {
		parent::initialize();
//        set_exception_handler(array(&$this, 'handle_exit'));
    }

    function _register_utilities()
    {
		$this->get_registry()->add_utility('I_Test_Controller', 'C_Test_Controller');
		$this->get_registry()->add_utility('I_Http_Response', 'C_Http_Response_Controller');
        $this->get_registry()->add_utility('I_Router', 'C_Router');
    }

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_NextGen_Activator', 'A_Activator_Rendering');
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_Routing_App_Factory');
	}

    function _register_hooks()
    {
        add_action('init', array(&$this, 'route'), 99);
    }

	function route()
	{
        // TODO: get gallery stub from settings
        $app = $this->get_registry()->get_utility('I_Router')->create_app();
		$app->route('/', array(
			'controller'	=>	'I_Test_Controller',
			'action'		=>	'index'
		));

        $app->rewrite('/nggallery/{album}/{gallery}/image/{pid}/',           '/album--{album}/gallery--{gallery}/pid--{pid}/');
        $app->rewrite('/nggallery/{album}/{gallery}/page-{page}/images/',    '/album--{album}/gallery--{gallery}/page--{page}/show--gallery/');
        $app->rewrite('/nggallery/{album}/{gallery}/page-{page}/slideshow/', '/album--{album}/gallery--{gallery}/page--{page}/show--slide/');
        $app->rewrite('/nggallery/{album}/{gallery}/page-{page}/',           '/album--{album}/gallery--{gallery}/page--{page}/');
        $app->rewrite('/nggallery/{album}/{gallery}/images/',                '/album--{album}/gallery--{gallery}/show--gallery/');
        $app->rewrite('/nggallery/{album}/{gallery}/slideshow/',             '/album--{album}/gallery--{gallery}/show--slide/');
        $app->rewrite('/nggallery/{album}/{gallery}/',                       '/album--{album}/gallery--{gallery}/');
        $app->rewrite('/nggallery/{album}/page-{page}/',                     '/album--{album}/page--{page}');
        $app->rewrite('/nggallery/{album}/',                                 '/album--{album}/');

        $app->rewrite('/nggallery/tags/{tag}/page-{page}/',  '/gallerytag--{tag}/page--{page}/');
        $app->rewrite('/nggallery/tags/{tag}/',              '/gallerytag--{tag}/');
        $app->rewrite('/nggallery/images/',                  '/show--gallery/');
        $app->rewrite('/nggallery/slideshow/',               '/show--slide/');
        $app->rewrite('/nggallery/image/{pid}/page-{page}/', '/pid--{pid}/page--{page}/');
        $app->rewrite('/nggallery/image/{pid}/',             '/pid--{pid}');
        $app->rewrite('/nggallery/page-{page}/',             '/page--{page}');

		$this->get_registry()->get_utility('I_Router')->serve_request();
	}

    function handle_exit($exception)
    {
        if (!($exception instanceof E_Clean_Exit))
			$this->rethrow = $exception;
    }

	function __destruct()
	{
		if ($this->rethrow)
            die(print_r($this->rethrow));
	}
}

new M_MVC();
