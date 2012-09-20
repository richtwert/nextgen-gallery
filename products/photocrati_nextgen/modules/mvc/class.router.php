<?php

/**
 * Provides a means for persisting the route cache
 */
class Mixin_Route_Persistence extends Mixin
{

}


/**
 * Provides routing pattern methods
 */
class Mixin_Route_Patterns extends Mixin
{
	function routing_uri($route = null, $only_path = null, $use_pathinfo = null, $add_trailing_slash=FALSE)
	{
		if ($use_pathinfo === null) {
			$permalink_struct = get_option('permalink_structure');
			$use_pathinfo = preg_match('/^\\/?index\\.php\\//i', $permalink_struct);
		}

		$uri = '/' . $route;
		if ($add_trailing_slash) $uri .= '/';

		if ($use_pathinfo) {
			$uri = '/index.php' . $uri;
		}

		if ($only_path) {
			return $uri;
		}

		return site_url($uri);
	}

	function routing_pattern($route = null, $pattern = null)
	{
		if ($pattern == null) {
			$pattern = '(\\w*)';
		}

		$uri = $this->object->routing_uri($route, TRUE, FALSE, FALSE);

		$pattern = '/' . preg_quote($uri, '/') . '(\/'.$pattern . ')?/';

		return $pattern;
	}
}

/**
 * Provides a means for adding/removing routes
 */
class Mixin_Router extends Mixin
{
    /**
     * Adds a named route, matching a pattern to a controller
     * @param string $name
     * @param array $pattern
     * @param string $controller_klass
     */
    function add_route($name, $controller_klass, $pattern, $singleton=FALSE)
    {
        $this->object->_routes[$name] = array($pattern, $controller_klass, $singleton);
        array_unshift($this->object->_route_priorities, $name);
    }

    /**
     * Removes a named route
    **/
    function remove_route($name)
    {
        unset($this->object->_routes[$name]);
    }


	/**
	 * Gets all registered named routes
	 * @param string $name	optionally specify which named route to retrieve
	 */
	function get_routes($name=FALSE)
	{
		$retval = $this->object->_routes;
		if ($name) {
			$retval = isset($retval[$name]) ? $retval[$name] : NULL;
		}

		return $retval;
	}

	/**
	 * Gets the metadata associated with a named route
	 * @param string $name
	 * @return array
	 */
	function get_named_route($name)
	{
		return $this->object->get_routes($name);
	}


    function route($exit=TRUE)
    {
        $domain = $_SERVER['SERVER_NAME'];
        $uri    = $_SERVER['REQUEST_URI'];
        $https  = isset($_SERVER['HTTPS']) ? TRUE: FALSE;
        $protocol = $https ? 'https' : 'http';

        $uri = preg_replace('/\\/index.php\\//', '/', $uri, 1);

        if ($this->object->is_cached($domain, $uri, $protocol)) {
            $this->object->call_cached_route($domain, $uri, $protocol, $exit);
        }

        foreach ($this->object->_route_priorities as $route_name) {
            $continue = TRUE;
            $config = $this->object->_routes[$route_name];
            $pattern = $config[0];
            $klass = $config[1];
            $singleton = $config[2];

            // The pattern is specified about HTTPS being on or off
            if (isset($pattern['https'])) {
                if ($pattern['https'] == FALSE && $https == TRUE) $continue = FALSE;
                elseif ($pattern['https'] == TRUE && $https == FALSE) $continue = FALSE;
            }

            // The pattern is specified about a domain requirement
            if ($continue && isset($pattern['domain'])) {
                if (!preg_match($pattern['domain'], $domain)) $continue = FALSE;
			}

            // Every pattern must specify a uri pattern
            if ($continue && preg_match($pattern['uri'], $uri, $match)) {
                // We've found a matching pattern!!!

                // A pattern can specify which matched set is the name of the action
                // Otherwise, we assume it's match[1]
                $action = isset($pattern['action']) ?
                    $match[$pattern['action']] :
                        (isset($match[2]) && trim(str_replace('&','',$match[2])) ?
                            $match[2] : 'index');

                // Cache the route for next time
                $this->object->cache_route(
                    $domain,
                    $uri,
                    $protocol,
                    $klass,
                    $action,
                    $singleton
                );

                // Call the action
                $this->object->call_cached_route($domain, $uri, $protocol, $exit);
				break;
            }
        }
    }

    /**
     * Caches a route for faster look up next time
     */
    function cache_route($domain, $uri, $protocol, $controller, $action, $singleton=FALSE)
    {
        if (!isset($this->object->_route_cache[$domain])) $this->object->_route_cache[$domain] = array();
        if (!isset($this->object->_route_cache[$domain][$protocol])) $this->object->_route_cache[$domain][$protocol] = array();

        $this->object->_route_cache[$domain][$protocol][$uri] = array($controller, $action, $singleton);
    }

    /**
     * Returns TRUE if the route is cached
     */
    function is_cached($domain, $uri, $protocol)
    {
        $retval = FALSE;

        if (isset($this->object->_route_cache[$domain])) {
            if (isset($this->object->_route_cache[$domain][$protocol])) {
                if (isset($this->object->_route_cache[$domain][$protocol][$uri])) $retval = TRUE;
            }
        }

        return $retval;
    }

    /**
     * Returns the cached route
     */
    function call_cached_route($domain, $uri, $protocol, $exit=TRUE)
    {
        $config = $this->object->_route_cache[$domain][$protocol][$uri];
        $klass = $config[0];
        $action = $config[1];
        $singleton = $config[2];

        // Each component has a context. For a controller, a context will
        // most likely we based on the request, so we'll let hooks figure it out
        $context = $this->object->get_context($domain, $uri, $protocol, $klass, $action);

        // TODO: We should probably be using a factory method here
        $controller = $singleton ?
            eval('return '.$klass.'::get_instance($context);') :
            new $klass($context);

        // Call the controller method
        $this->object->call_action($controller, $action.'_action', $exit);

        // If debug, show some debugging information
        if ($controller->debug) {
            echo implode("\n", array(
                '<pre>',
                'Execution Time: '.(microtime() - PHOTOCRATI_GALLERY_PLUGIN_STARTED_AT),
                'Memory Consumption: '.(memory_get_usage(TRUE)/1000/1000).'MB',
                'Peaked Memory Consumption: '.(memory_get_peak_usage(TRUE)/100/1000).'MB',
                '</pre>'
            ));
        }

        // We've finished routing the request.
        // Since the plugin is currently routed within WordPress, we need to
        // tell WordPress that we're finished as well. In the past, I've been
        // calling exit() but that isn't recommended to do in FastCGI
        // environments. See http://serverfault.com/questions/84962/php-via-fastcgi-terminated-by-calling-exit
        //
        if ($exit) throw new E_Clean_Exit();
    }

    /**
     * Returns the context for the controller
     * Hooks should extend this
     */
    function get_context($domain, $uri, $protocol, $class, $action)
    {
        return FALSE;
    }

    /**
     * Calls an action of a controller
     * Hooks should extend this
     */
    function call_action($controller, $action, $exit=FALSE)
    {
		$controller->exit = $exit;
        call_user_func(array($controller, $action));
    }
}

/**
 * A router is configured to match patterns against a url
 * and route the request to a particular controller and action
 */
class C_Router extends C_Component
{
    static $_instances = array();
    var $_routes = array();
    var $_route_priorities = array();
    var $_route_cache = array();

    function define($context=FALSE)
    {
		parent::define($context);
        $this->add_mixin('Mixin_Router');
		$this->add_mixin('Mixin_Route_Patterns');
		$this->implement('I_Router');
    }

    static function &get_instance($context = False)
    {
		if (!isset(self::$_instances[$context])) {
			self::$_instances[$context] = new C_Router($context);
		}
		return self::$_instances[$context];
    }
}
