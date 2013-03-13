<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


class Mixin_MVC_Controller_Defaults extends Mixin
{
    // Provide a default view
    function index_action($return=FALSE)
    {
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
		$this->add_mixin('Mixin_MVC_Controller_Instance_Methods');
        $this->implement('I_MVC_Controller');
    }
}

/**
 * Adds methods for MVC Controller
 */
class Mixin_MVC_Controller_Instance_Methods extends Mixin
{
	function set_content_type($type)
    {
        switch ($type) {
            case 'html':
            case 'xhtml':
                $type = 'text/html';
                break;
			case 'xml':
				$type = 'text/xml';
				break;
			case 'rss':
			case 'rss2':
				$type = 'application/rss+xml';
				break;
            case 'css':
                $type = 'text/css';
                break;
            case 'javascript':
            case 'jscript':
            case 'emcascript':
                $type = 'text/javascript';
                break;
			case 'json':
				$type = 'application/json';
				break;
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                $type = 'image/jpeg';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            case 'png':
                $type = 'image/x-png';
                break;
            case 'tiff':
            case 'tif':
                $type = 'image/tiff';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
        }

        $this->object->_content_type = $type;
        return $type;
    }

	function do_not_cache()
	{
		if (!headers_sent()) {
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}
	}

	function expires($time)
	{
		$time = strtotime($time);
		if (!headers_sent()) {
			header('Expires: '.strftime("%a, %d %b %Y %T %Z", $time));
		}
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


	function get_router()
	{
		return $this->object->get_registry()->get_utility('I_Router');
	}

	function get_routed_app()
	{
		return $this->object->get_router()->get_routed_app();
	}

    /**
     * Returns the value of a parameters
     * @param string $key
     * @return mixed
     */
    function param($key, $prefix = NULL, $default = NULL)
    {
		return $this->object->get_routed_app()->get_parameter($key, $prefix, $default);
    }

	function set_param($key, $value, $id=NULL, $use_prefix=FALSE)
	{
		return $this->object->get_routed_app()->set_parameter($key, $value, $id, $use_prefix);
	}

	function set_param_for($url, $key, $value, $id=NULL, $use_prefix=FALSE)
	{
		return $this->object->get_routed_app()->set_parameter($key, $value, $id, $use_prefix, $url);
	}

	function remove_param($key, $id=NULL)
	{
		return $this->object->get_routed_app()->remove_parameter($key, $id);
	}

	function remove_param_for($url, $key, $id=NULL)
	{
		$app = $this->object->get_routed_app();
		$retval = $app->remove_parameter($key, $id, $url);
		return $retval;
	}

	/**
	 * Gets the routed url, generated by the Routing App
	 * @return string
	 */
	function get_routed_url($with_qs=FALSE)
	{
		return $this->object->get_routed_app()->get_app_url(FALSE, $with_qs);
	}

	/**
	 * Gets the absolute path of a static resource
	 * @param string $path
	 * @param string $module
	 * @param boolean $relative
	 * @return string
	 */
	function get_static_abspath($path, $module=FALSE, $relative=FALSE)
	{
		return $this->get_registry()->get_utility('I_Fs')->find_static_abspath(
			$path, $module
		);
	}

	/**
	 * Gets the relative path of a static resource
	 * @param string $path
	 * @param string $module
	 * @return string
	 */
	function get_static_relpath($path, $module=FALSE)
	{
		return $this->get_registry()->get_utility('I_Fs')->find_static_abspath(
			$path, $module, TRUE
		);
	}


	function get_static_url($path, $module=FALSE)
	{
		return $this->get_registry()->get_utility('I_Router')->get_static_url(
			$path, $module
		);
	}

	/**
	 * Gets the absolute path of an MVC template file
	 * @param string $path
	 * @param string $module
	 * @return string
	 */
	function find_template_abspath($path, $module=FALSE)
	{
		$fs			= $this->get_registry()->get_utility('I_Fs');
		$settings	= $this->get_registry()->get_utility('I_Settings_Manager');

		// We also accept module_name#path, which needs parsing.
		if (!$module) list($path, $module) = $fs->parse_formatted_path($path);

		// Append the suffix
		$filename	= $path.'.php';

		// Find the template
		$retval = $fs->find_abspath($fs->join_paths($settings->mvc_template_dirname, $filename), $module);
		if (!$retval) throw new RuntimeException("{$path} is not a valid MVC template");
		return $retval;
	}

	/**
	 * Renders a template and outputs the response headers
	 * @param string $name
	 * @param array $vars
	 */
    function render_view($name, $vars=array())
    {
		$this->object->render();
        $this->object->render_partial($name, $vars);
    }


	/**
	 * Outputs the response headers
	 */
	function render()
	{
		if (!headers_sent()) header('Content-Type: '.$this->object->_content_type);
	}


    /**
     * Renders a view
     */
    function render_partial($__name, $__vars=array(), $__return=FALSE)
    {
        // If the template given is an absolute path, then use that - otherwise find the template
        $__filename = (strpos($__name, '/') === 0 && file_exists($__name)) ? $__name: $this->object->find_template_abspath($__name);
        ob_start();
        extract((array)$__vars);
        include($__filename);
        $__content = ob_get_clean();
        if ($__return)
            return $__content;
        else
            echo $__content;
    }
}
