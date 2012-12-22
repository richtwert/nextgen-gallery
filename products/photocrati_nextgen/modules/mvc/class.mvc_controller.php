<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


class Mixin_MVC_Controller_Defaults extends Mixin
{
    // Provide a default view
    function index_action($return=FALSE)
    {
        $this->debug = TRUE;
        return $this->render_view('index', array(), $return);
    }
}


/**
 * Provides actions that are executed based on the requested url
 */
abstract class C_MVC_Controller extends C_Component
{
    var $_content_type	= 'text/html';
	var $message		= '';
    var $debug			= FALSE;


    function define($context=FALSE)
    {
		parent::define($context);
        $this->add_mixin('Mixin_MVC_Controller_Defaults');
		$this->add_mixin('Mixin_MVC_Controller_Rendering');
		$this->add_mixin('Mixin_MVC_Controller_Instance_Methods');
        $this->implement('I_MVC_Controller');
    }


    function http_error($message, $code=500)
    {
		$this->message = $message;
		$method = "http_{$code}_action";
		$this->$method();
    }

    function is_valid_request($method)
    {
        return TRUE;
    }


    function is_post_request()
    {
        return "POST" == $this->object->get_router()->get_request_method();
    }


    function is_get_request()
    {
        return "GET" == $this->object->get_router()->get_request_method();
    }


    function is_delete_request()
    {
       return "DELETE" == $this->object->get_router()->get_request_method();
    }


    function is_put_request()
    {
        return "PUT" == $this->object->get_router()->get_request_method();
    }


    function is_custom_request($type)
    {
        return strtolower($type) == strtolower($this->object->get_router()->get_request_method());
    }

    /**
     * Returns the value of a parameters
     * @param string $key
     * @return mixed
     */
    function param($key, $prefix = NULL, $default = NULL)
    {
        $retval = $default;
        $router = $this->object->get_registry()->get_utility('I_Router');
        $result = $router->get_routed_app()->get_parameter($key, $prefix);
        return (!is_null($result) ? $result : $retval);
    }

    // Validates the request before executing an action. If no action has
    // been defined, then return 404
    function __call($method, $args) {
        $retval = '';
		if (preg_match("/_action$/", $method)) {
            if ($this->is_valid_request($method)) {
                $throw = $this->_throw_error;
                $this->_throw_error = FALSE;
				if ($this->has_method($method) || method_exists($this, $method))
					$retval = parent::__call ($method, $args);
				else
                    $retval = $this->http_error("Page Not Found", 404);
                $this->_throw_error = $throw;
            }
        }
        else $retval = parent::__call ($method, $args);

        return $retval;
    }

	/**
	 * Gets the relative URL of the current request
	 * @return string
	 */
	function get_relative_url($segment='')
	{
		return isset($_SERVER['REQUEST_URI']) ? path_join($_SERVER['REQUEST_URI'], $segment) : '';
	}


	/**
	 * Gets the absolute url of the current request
	 * @return string
	 */
	function get_absolute_url($segment='')
	{
		$url = $this->object->get_relative_url($segment);
		return $url ? real_site_url($url) : '';
	}
}

/**
 * Adds methods for MVC Controller
 */
class Mixin_MVC_Controller_Instance_Methods extends Mixin
{
	function do_not_cache()
	{
		if (!headers_sent()) {
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}
	}
}
