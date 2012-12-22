<?php

class Mixin_Router extends Mixin
{
	function set_routed_app($app)
    {
        $this->_routed_app = $app;
    }

    function get_routed_app()
    {
        return $this->_routed_app;
    }

	/**
	 * Gets url for the router
	 * @param string $uri
	 * @return string
	 */
	function get_url($uri='/')
	{
		return $this->object->get_base_url().$uri;
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
    static $_instances = array();
	var $_apps = array();

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
