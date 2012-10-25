<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


class Mixin_MVC_Controller_Defaults extends Mixin
{
    // Provide a default view
    function index_action($return=FALSE)
    {
        $this->debug = TRUE;
        return $this->render_partial('index', array(), $return);
    }
}


/**
 * Provides actions that are executed based on the requested url
 */
abstract class C_MVC_Controller extends C_Component
{
    var $_content_type = 'text/html';
    var $_request = FALSE;
    var $_params = array();
    var $_request_method = "None";
    var $debug = FALSE;
	var $exit = FALSE;


    function define($context=FALSE)
    {
		parent::define($context);
        $this->add_mixin('Mixin_MVC_Controller_Defaults');
		$this->add_mixin('Mixin_MVC_Controller_Rendering');
		$this->add_mixin('Mixin_MVC_Controller_Instance_Methods');
        $this->implement('I_MVC_Controller');
    }

    function initialize()
    {
        parent::initialize();
        $this->_request = function_exists('apache_request_headers') ?
            apache_request_headers() : array();
        $this->_params = $this->parse_params($_REQUEST);
        $this->_request_method = $_SERVER['REQUEST_METHOD'];
    }


    function parse_params($arr)
    {
        $retval = array();

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = $this->parse_params($value);
            }
            elseif (is_string($value)) {
                if ($value == 'true') $value = TRUE;
                elseif ($value == 'false') $value = FALSE;
                elseif ($value == 'null') $value = NULL;
            }

            // Update the value
            $retval[$key] = $value;
        }

        return $retval;
    }


    function show_error($message, $code=500)
    {
        if (!headers_sent()) header("HTTP/1.0 {$code} {$message}");
        $this->render_view($code, array('message' => $message));
        if ($this->exit) throw new E_Clean_Exit();
    }

    function is_valid_request($method)
    {
        return TRUE;
    }


    function is_post_request()
    {
        return "POST" == $this->_request_method;
    }


    function is_get_request()
    {
        return "GET" == $this->_request_method;
    }


    function is_delete_request()
    {
       return "DELETE" == $this->_request_method;
    }


    function is_put_request()
    {
        return "PUT" == $this->_request_method;
    }


    function is_custom_request($type)
    {
        return strtolower($type) == strtolower($this->_request_method);
    }

    /**
     * Returns the value of a parameters
     * @param string $key
     * @return mixed
     */
    function &param($key, $default=NULL)
    {
        $retval = $default;

        if (isset($this->_params[$key])) {
            $val = &$this->_params[$key];
            if (is_array($val))
                $retval = &$val;
            elseif (is_string($val) && !in_array(strtolower($val), array('null','false')))
                $retval = &$val;
        }

        return $retval;
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
                    $retval = $this->show_error("Page Not Found", 404);
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
		return isset($_SERVER['REQUEST_URI']) ?
			path_join($_SERVER['REQUEST_URI'], $segment) : '';
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
