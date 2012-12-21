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

    function get_request_uri()
    {
		if (isset($_SERVER['PATH_INFO']))
			$retval = $_SERVER['PATH_INFO'];
		else
			$retval = $_SERVER['REQUEST_URI'];

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
		if ($context == 'all') $context = FALSE;
		parent::define($context);
        $this->add_mixin('Mixin_Router');
		$this->implement('I_Router');
    }

	function initialize()
	{
		parent::initialize();;
		if (!$this->context) $this->context = $this->object->get_request_uri();
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
