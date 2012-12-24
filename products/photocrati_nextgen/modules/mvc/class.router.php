<?php

class Mixin_Router extends Mixin
{
	function set_routed_app($app)
    {
        $this->object->_routed_app = $app;
    }

    function get_routed_app()
    {
        return $this->object->_routed_app ? $this->object->_routed_app : $this->object->get_default_app();
    }

	function get_default_app()
	{
		if (is_null($this->object->_default_app))
			$this->object->_default_app = $this->object->create_app();
		return $this->object->_default_app;
	}

	function route($patterns, $handler=FALSE)
	{
		$this->object->get_default_app()->route($patterns, $handler);
	}

	function rewrite($src, $dst, $redirect=FALSE)
	{
		$this->object->get_default_app()->rewrite($src, $dst, $redirect);
	}

	function get_parameter($key, $prefix=NULL, $default=NULL)
	{
		return $this->object->get_routed_app()->get_parameter($key, $prefix, $default);
	}

	function param($key, $prefix=NULL, $default=NULL)
	{
		return $this->object->get_parameter($key, $prefix, $default);
	}

	/**
	 * Gets url for the router
	 * @param string $uri
	 * @return string
	 */
	function get_url($uri='/', $with_qs=FALSE)
	{
		$retval = $this->object->get_base_url().$uri;
		if ($with_qs && ($qs = $this->object->get_querystring())) {
			$retval .= '?'.$qs;
		}
		return $retval;
	}

	/**
	 * Gets the routed url
	 * @returns string
	 */
	function get_routed_url()
	{
		$retval = $this->object->get_url($this->object->get_request_uri());

		if (($app = $this->object->get_routed_app())) {
			$retval = $this->object->get_url($app->get_app_uri());
		}

		return $retval;
	}

	/**
	 * Gets the base url for the router
	 * @return string
	 */
	function get_base_url()
	{
		$protocol = $this->object->is_https()? 'https://' : 'http://';
		$retval = "{$protocol}{$_SERVER['SERVER_NAME']}{$this->object->context}";
		if (substr($retval, -1) == '/') $retval = substr($retval, 0, -1);
		return $retval;
	}

	function join_paths()
	{
		$segments = func_get_args();
		foreach ($segments as &$segment) {
			if (strpos($segment, '/') === 0) $segment = substr($segment, 1);
		}
		$retval = implode('/', $segments);
		if (strpos($retval, '/') !== 0) $retval = '/'.$retval;

		return $retval;
	}

	/**
	 * Determines if the current request is over HTTPs or not
	 */
	function is_https()
	{
		return isset($_SERVER['HTTPS']);
	}


    /**
     * Serve request using defined Routing Apps
     *
     * @param string|FALSE $request_uri
     */
    function serve_request()
    {
        // iterate over all apps, and serve the route
        foreach ($this->object->get_apps() as $app) {
            if ($app->serve_request($this->object->context))
                break;
        }
    }

	/**
	 * Gets the querystring of the current request
	 * @return string
	 */
	function get_querystring()
	{
		return $_SERVER['QUERY_STRING'];
	}


	function set_querystring($value)
	{
		$_SERVER['QUERY_STRING'] = $value;
	}

	/**
	 * Gets the request for the router
	 * @return string
	 */
    function get_request_uri()
    {
		if (isset($_SERVER['PATH_INFO']))
			$retval = $_SERVER['PATH_INFO'];
		else
			$retval = $_SERVER['REQUEST_URI'];

		$retval = preg_replace('#^'.preg_quote($this->object->context).'#', '', $retval);
		if (strpos($retval, '/') !== 0) $retval = "/{$retval}";

		return $retval;
    }

	/**
	 * Gets the method of the HTTP request
	 * @return string
	 */
	function get_request_method()
	{
		return $this->object->_request_method;
	}


    function &create_app($name = '/')
    {
        $factory = $this->get_registry()->get_utility('I_Component_Factory');
        $app = &$factory->create('routing_app', $name);
        $this->object->_apps[] = $app;
        return $app;
    }

    /**
     * Gets a list of apps registered for the router
     *
     * @return array
     */
    function get_apps()
    {
        return $this->object->_apps;
    }
}

/**
 * A router is configured to match patterns against a url and route the request to a particular controller and action
 */
class C_Router extends C_Component
{
    static $_instances	= array();
	var $_apps			= array();
	var $_default_app	= NULL;

    function define($context = FALSE)
    {
		if (!context OR $context == 'all') $context = '/';
		parent::define($context);
        $this->add_mixin('Mixin_Router');
		$this->implement('I_Router');
    }

	function initialize()
	{
		parent::initialize();
		$this->_request_method	= $_SERVER['REQUEST_METHOD'];
	}

    static function &get_instance($context = False)
    {
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
    }
}
