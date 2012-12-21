<?php

class Mixin_Router extends Mixin
{
    public $_apps = array();
    public $_request_uri = '';
    public $_routed_app = NULL;

    public function initialize()
    {
        parent::initialize();

        if (empty($this->_request_uri))
            $this->object->set_request_uri($this->object->get_request_uri(FALSE), TRUE);
    }

    public function set_routed_app($app)
    {
        $this->_routed_app = $app;
    }

    public function get_routed_app()
    {
        return $this->_routed_app;
    }

    /**
     * Serve request using defined Routing Apps
     *
     * @param string|FALSE $request_uri
     */
    public function serve_request($request_uri = NULL)
    {
        // ensure we have a request uri to serve
        if (!$request_uri)
            $request_uri = $this->object->get_request_uri();

        // iterate over all apps, and serve the route
        foreach ($this->object->get_apps() as $app) {
            if ($app->serve_request($request_uri))
                $this->object->set_routed_app($app);
                break;
        }
    }

    public function set_request_uri($uri, $internal = TRUE)
    {
        if ($internal)
        {
            $this->_request_uri = $uri;
        }
        else {
            if (isset($_SERVER['PATH_INFO']))
                $_SERVER['PATH_INFO'] = $uri;
            else
                $_SERVER['REQUEST_URI'] = $uri;
        }
    }

    public function get_request_uri($internal = TRUE)
    {
        if ($internal)
        {
            $retval = $this->_request_uri;
        }
        else {
            if (isset($_SERVER['PATH_INFO']))
                $retval = $_SERVER['PATH_INFO'];
            else
                $retval = $_SERVER['REQUEST_URI'];
        }

        return $retval;
    }


    public function &create_app($name = '/')
    {
        $factory = $this->get_registry()->get_utility('I_Component_Factory');
        $app = $factory->create('routing_app', $name, $this);
        $this->_apps[] = $app;
        return $app;
    }

    /**
     * Gets a list of apps registered for the router
     *
     * @return array
     */
    public function get_apps()
    {
        return $this->_apps;
    }
}

/**
 * A router is configured to match patterns against a url and route the request to a particular controller and action
 */
class C_Router extends C_Component
{
    static $_instances = array();

    function define($context = FALSE)
    {
		parent::define($context);
        $this->add_mixin('Mixin_Router');
		// $this->implement('I_Router');
    }

    static function &get_instance($context = False)
    {
		if (!isset(self::$_instances[$context])) {
			self::$_instances[$context] = new C_Router($context);
		}
		return self::$_instances[$context];
    }
}
