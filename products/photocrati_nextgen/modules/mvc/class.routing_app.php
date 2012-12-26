<?php
class Mixin_Routing_App extends Mixin
{
    /**
     * Creates a new route endpoint with the assigned handler
     *
     * @param array $routes URL to route, eg /page/{page}/
     * @param array $handler Formatted array
     */
    function route($routes, $handler)
    {
        // ensure that the routing patterns array exists
        if (!is_array($this->object->_routing_patterns))
            $this->object->_routing_patterns = array();

        if (!is_array($routes))
            $routes = array($routes);

        // fetch all routing patterns
        $patterns = $this->object->_routing_patterns;

        foreach ($routes as $route) {
            // add the routing pattern
            $patterns[$this->object->_route_to_regex($route)] = $handler;
        }

        // update routing patterns
        $this->object->_routing_patterns = $patterns;
    }

    /**
     * Handles internal url rewriting with optional HTTP redirection,
     *
     * @param string $src Original URL
     * @param string $dst Destination URL
     * @param bool $redirect FALSE for internal handling, otherwise the HTTP code to send
     */
    function rewrite($src, $dst, $redirect = FALSE)
    {
        // ensure that rewrite patterns array exists
        if (!is_array($this->object->_rewrite_patterns))
            $this->object->_rewrite_patterns = array();

        // fetch all rewrite patterns
        $patterns = $this->object->_rewrite_patterns;

        // add the rewrite pattern
        $patterns[$this->object->_route_to_regex($src)] = array('dst' => $dst, 'redirect' => $redirect);

        // update rewrite patterns;
        $this->object->_rewrite_patterns = $patterns;
    }

	/**
	 * Gets an instance of the router
	 * @return type
	 */
	function get_router()
	{
		return $this->object->get_registry()->get_utility('I_Router');
	}

	function get_app_url($request_uri=FALSE, $with_qs=FALSE)
	{
		return $this->object->get_router()->get_url($this->object->get_app_uri($request_uri), $with_qs);
	}


	function get_routed_url($with_qs=TRUE)
	{
		return $this->object->get_app_url(FALSE, $with_qs);
	}

	function get_app_uri($request_uri=FALSE)
	{
		if (!$request_uri) $request_uri = $this->object->get_app_request_uri();
		return $this->object->join_paths(
			$this->object->context,
			$request_uri
		);
	}

	function join_paths()
	{
		$segments = func_get_args();
		foreach ($segments as &$segment) {
			if (strpos($segment, '/') === 0) $segment = substr($segment, 1);
			if (substr($segment, -1) == '/') $segment = substr($segment, -1);
		}
		$retval = implode('/', $segments);
		if (strpos($retval, '/') !== 0 && strpos($retval, 'http') === FALSE) $retval = '/'.$retval;

		return $retval;
	}

	function get_app_request_uri()
	{
		$retval = FALSE;

		if ($this->object->_request_uri) $retval = $this->object->_request_uri;
		else if (($match = $this->object->does_app_serve_request())) {
			$retval = str_replace($match['match'], '', $match['request_uri']);
			if (!$retval) $retval = '/';
			$this->object->set_app_request_uri($retval);
		}

		return $retval;
	}

	/**
	 * Sets the application request uri
	 * @param type $uri
	 */
	function set_app_request_uri($uri)
	{
		$this->object->_request_uri = $uri;
	}

	/**
	 * Gets the application's routing regex pattern
	 * @return string
	 */
	function get_app_routing_pattern()
	{
		$segment = $this->object->context;
		$segment = (substr($this->object->context, 0) == '/' ? '^':'').$segment;
		return preg_quote('#'.$segment.'#i');
	}


	/**
	 * Determines whether this app serves the request
	 * @return boolean|array
	 */
	function does_app_serve_request()
	{
		$retval = FALSE;

		// get the request uri to match
        $request_uri			= $this->object->get_router()->get_request_uri();
		$app_routing_pattern	= $this->object->get_app_routing_pattern();

		if (preg_match($app_routing_pattern, $request_uri, $matches)) {
			$retval = array(
				'match'			=>	array_pop($matches),
				'request_uri'	=>	$request_uri
			);
		}

		return $retval;
	}

    /**
     * Determines if the current routing app meets our requirements and serves them
     *
     * @return bool
     * @throws E_Clean_Exit
     */
    function serve_request()
    {
        $served = FALSE;

        // ensure that the routing patterns array exists
        if (!is_array($this->object->_routing_patterns))
            $this->object->_routing_patterns = array();

        // ensure that rewrite patterns array exists
        if (!is_array($this->object->_rewrite_patterns))
            $this->object->_rewrite_patterns = array();

        // if the application root matches, then we'll try to route the request
        if (($request_uri = $this->object->get_app_request_uri())){
			$served = TRUE;
			$redirect = FALSE;

            // start rewriting urls
            foreach ($this->object->_rewrite_patterns as $pattern => $details) {

                if (preg_match_all($pattern, $request_uri, $matches, PREG_SET_ORDER))
				{
					// Assign new request URI
					$request_uri = $details['dst'];

					// Substitute placeholders
					foreach ($matches as $match) {
						if ($redirect) break;
						foreach ($match as $key => $val) {

							// If we have a placeholder that needs swapped, swap
							// it now
							if (is_numeric($key)) continue;
							$request_uri = str_replace("{{$key}}", $val, $request_uri);
						}
						// Set the redirect flag if we're to do so
						if (isset($details['redirect']) && $details['redirect']) {
							$redirect = $details['redirect'] === TRUE ?
								302 : intval($details['redirect']);
							break;
						}

					}
                }
            }

			// Cache all known data about the application request
			$this->object->set_app_request_uri($request_uri);
			$this->object->get_router()->set_routed_app($this);

			// Are we to perform a redirect?
			if ($redirect) {
				$this->object->execute_route_handler(
					$this->object->parse_route_handler($redirect)
				);
			}

			// Handle routed endpoints
			else {
				foreach ($this->object->_routing_patterns as $pattern => $handler) {
					if (preg_match($pattern, $request_uri, $matches)) {
						$this->object->add_placeholder_params_from_matches($matches);

						// If a handler is attached to the route, execute it. A
						// handler can be
						// - FALSE, meaning don't do any post-processing to the route
						// - A string, such as controller#action
						// - An array: array(
						//   'controller' => 'I_Test_Controller',
						//   'action'	  => 'index',
						//   'context'	  => 'all', (optional)
						//   'method'	  => array('GET') (optional)
						// )
						if ($handler && $handler = $this->object->parse_route_handler($handler)) {

							// Is this handler for the current HTTP request method?
							if (isset($handler['method'])) {
								if (!is_array($handler['method'])) $handler['$method'] = array($handler['method']);
								if (in_array($this->object->get_router()->get_request_method(), $handler['method'])) {
									$this->object->execute_route_handler($handler);
								}
							}

							// This handler is for all request methods
							else {
								$this->object->execute_route_handler($handler);
							}
						}
					}
				}
			}
        }

        return $served;
    }

	/**
	 * Executes an action of a particular controller
	 * @param array $handler
	 * @throws E_Clean_Exit
	 */
	function execute_route_handler($handler)
	{
		// Get action
		$action = $handler['action'];

		// Get controller
		$controller = $this->object->get_registry()->get_utility(
			$handler['controller'], $context
		);

		// Call action
		$controller->$action();

		// Clean Exit (fastcgi safe)
		throw new E_Clean_Exit;
	}

	/**
	 * Parses the route handler
	 * @param mixed $handler
	 * @return array
	 */
	function parse_route_handler($handler)
	{
		if (is_string($handler)) {
			$handler = array_combine(array('controller', 'action'), explode('#', $handler));
		}
		elseif (is_numeric($handler)) {
			$handler = array(
				'controller'	=>	'I_Http_Response',
				'action'		=>	'http_'.$handler,
			);
		}
		if (!isset($handler['context'])) $handler['context'] = FALSE;
		if (strpos($handler['action'], '_action') === FALSE) $handler['action'] .= '_action';

		return $handler;
	}


	function add_placeholder_params_from_matches($matches)
	{
		// Add the placeholder parameter values to the _params array
		foreach ($matches as $key => $value) {
			if (is_numeric($key)) continue;
			$this->object->add_placeholder_param(
				$key, $value, $matches[0]
			);
		}
	}


	/**
	 * Adds a placeholder parameter
	 * @param string $name
	 * @param stirng $value
	 * @param string $source
	 */
	function add_placeholder_param($name, $value, $source=NULL)
	{
		if (!is_array($this->object->_parameters)) {
			$this->object->_parameters = array('global');
		}
		if (!isset($this->object->_parameters['global'])) {
			$this->object->_parameters['global'] = array();
		}
		$this->object->_parameters['global'][] = array(
			'id'	=>	'',
			'name'	=>	$name,
			'value'	=>	$value,
			'source'=>	$source
		);
	}

    /**
     * Converts the route to the regex
     *
     * @param string $route
     * @return string
     */
    function _route_to_regex($route)
    {
        // convert route to RegEx pattern
        $route_regex = preg_quote(
            str_replace(
                array('{', '}'),
                array('~', '~'),
                $route
            )
        );
		if (strpos($route, '/') === 0) $route_regex = '^'.$route_regex;

		// A route can be followed by a list of parameters
		$separator = preg_quote(MVC_PARAM_SEPARATOR);
		$param_part_regex = "[^{$separator}]+";
		$param_regex = "(({$param_part_regex}{$separator})?{$param_part_regex}{$separator}{$param_part_regex}\/?){0,}";

        $route_regex = '#' . $route_regex . $param_regex . '$#i';

        // convert placeholders to regex as well
        return preg_replace('/~([^~]+)~/', '(?<\1>[^/]+)/?', $route_regex);
    }

	/**
	 * Gets a request parameter from either the request uri or querystring
	 * This method takes into consideration the values of the MVC_PARAM_PREFIX
	 * and MVC_PARAM_SEPARATOR constants when searching for the parameter
	 *
	 * Parameter can take on the following forms:
	 * /key--value
	 * /[MVC_PARAM_PREFIX]key--value
	 * /[MVC_PARAM_PREFIX]-key--value
	 * /[MVC_PARAM_PREFIX]_key--value
	 * /id--key--value
	 * /id--[MVC_PARAM_PREFIX]key--value
	 * /id--[MVC_PARAM_PREFIX]-key--value
	 * /id--[MVC_PARAM_PREFIX]_key--value
	 *
	 * @param string $key
	 * @param mixed $id
	 * @param mixed $default
	 * @return mixed
	 */
	function get_parameter($key, $id=NULL, $default=NULL, $segment=FALSE, $url=FALSE)
	{
		$retval				= $default;
		$key				= preg_quote($key);
		$id					= $id ? preg_quote($id) : "\d+";
		$param_prefix		= preg_quote(MVC_PARAM_PREFIX);
		$param_sep			= preg_quote(MVC_PARAM_SEPARATOR);
		$param_regex		= "#/((?<id>{$id}){$param_sep})?({$param_prefix}[-_]?)?{$key}{$param_sep}(?<value>[^\/]+)\/?#i";
		$found				= FALSE;
		$sources			= $url ? array('custom' => $url) : $this->object->get_parameter_sources();

		foreach ($sources as $source_name => $source) {
			if (preg_match($param_regex, $source, $matches)) {
				if ($segment) $retval = array(
					'segment'		=>	$matches[0],
					'source'		=>	$source_name
				);
				else $retval = $matches['value'];
				$found = TRUE;
				break;
			}
		}

		// Lastly, check the $_REQUEST
		if (!$found && !$url && isset($_REQUEST[$key])) $retval = $_REQUEST[$key];

		return $retval;
	}

	/**
	 * Sets the value of a particular parameter
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $id
	 */
	function set_parameter_value($key, $value, $id=NULL, $use_prefix=FALSE, $url=FALSE)
	{
		// Remove the parameter from both the querystring and request uri
		$retval = $this->object->remove_parameter($key, $id, $url);

		// We're modifying the current request
		if ($url) {
			$retval = $this->object->join_paths(
				$retval,
				$this->object->create_parameter_segment($key, $value, $id, $use_prefix)
			);
		}

		// We're modifying the current request
		else {
			// This parameter is being appended to the current request uri
			$this->object->add_parameter_to_app_request_uri($key, $value, $id, $use_prefix);

			// Return the new full url
			$this->object->get_routed_url();
		}

		return $retval;
	}

	/**
	 * Alias for remove_parameter()
	 * @param string $key
	 * @param mixed $id
	 * @return string
	 */
	function remove_param($key, $id=NULL, $url=FALSE)
	{
		return $this->object->remove_parameter($key, $id, $url);
	}

	/**
	 * Removes a parameter from the querystring and application request URI
	 * and returns the full application URL
	 * @param string $key
	 * @param mixed $id
	 * @return string
	 */
	function remove_parameter($key, $id=NULL, $url=FALSE)
	{
		$retval			= $url;
		$param_sep		= MVC_PARAM_SEPARATOR;
		$param_prefix	= MVC_PARAM_PREFIX;

		// Is the parameter already part of the request? If so, modify that
		// parmaeter
		if (($segment = $this->object->get_parameter_segment($key, $id, $url))) {
 			extract($segment);

			if ($source == 'querystring') {
				$preg_id	= $id ? '\d+' : preg_quote($id);
				$preg_key	= preg_quote($key);
				$regex = implode('', array(
					'#',
					$id ? "{$preg_id}{$param_sep}" : '',
					"(({$param_prefix})?[-_]?)?{$preg_key}({$param_sep}|=)[^\/&]+&?#i"
				));
				$qs = preg_replace($regex, '', $this->get_router()->get_querystring());
				$this->object->get_router()->set_querystring($qs);
				$retval = $this->object->get_routed_url();
			}
			elseif ($source == 'request_uri') {
				$uri = $this->object->get_app_request_uri();
				$uri = str_replace($segment, '', $uri);
				$this->object->set_app_request_uri($uri);
				$retval = $this->object->get_routed_url();
			}
			else {
				$retval = str_replace($segment, '', $retval);
			}
		}

		return $retval;
	}


	/**
	 * Adds a parameter to the application's request URI
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $id
	 */
	function add_parameter_to_app_request_uri($key, $value, $id=NULL, $use_prefix=FALSE)
	{
		$this->object->set_app_request_uri(
			$this->object->join_paths(
				$this->object->get_app_request_uri(),
				$this->object->create_parameter_segment($key, $value, $id, $use_prefix)
			)
		);

		return $this->object->get_app_request_uri();
	}


	/**
	 * Creates a parameter segment
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $id
	 * @return string
	 */
	function create_parameter_segment($key, $value, $id=NULL, $use_prefix=FALSE)
	{
		if ($use_prefix) $key = MVC_PARAM_PREFIX.$key;
		if ($value === TRUE) $value = 1;
		elseif ($value == FALSE) $value = 0; // null and false values
		$retval = $key . MVC_PARAM_SEPARATOR . $value;
		if ($id) $retval = $id . MVC_PARAM_SEPARATOR . $retval;
		return $retval;
	}

	/**
	 * Alias for set_parameter_value
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $id
	 */
	function set_parameter($key, $value, $id=NULL, $use_prefix=FALSE, $url=FALSE)
	{
		return $this->object->set_parameter_value($key, $value, $id, $use_prefix, $url);
	}

	/**
	 * Alias for set_parameter_value
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $id
	 */
	function set_param($key, $value, $id=NULL, $use_prefix=FALSE, $url=FALSE)
	{
		return $this->object->set_parameter_value($key, $value, $id, $use_prefix=FALSE, $url);
	}

	/**
	 * Gets a parameter's value
	 * @param string $key
	 * @param mixed $id
	 * @param mixed $default
	 * @return mixed
	 */
	function get_parameter_value($key, $id=NULL, $default=NULL, $url=FALSE)
	{
		return $this->object->get_parameter($key, $id, $default, FALSE, $url);
	}

	/**
	 * Gets a parameter's matching URI segment
	 * @param string $key
	 * @param mixed $id
	 * @param mixed $default
	 * @return mixed
	 */
	function get_parameter_segment($key, $id=NULL, $url=FALSE)
	{
		return $this->object->get_parameter($key, $id, NULL, TRUE, $url);
	}

	/**
	 * Gets sources used for parsing and extracting parameters
	 * @return array
	 */
	function get_parameter_sources()
	{
		return array(
			'querystring'	=>	$this->object->get_formatted_querystring(),
			'request_uri'	=>	$this->object->get_app_request_uri(),
			'postdata'		=>	$this->object->get_postdata()
		);
	}

	function get_postdata()
	{
		$retval = '/'.file_get_contents("php://input");
		$retval = str_replace(
			array('&', '='),
			array('/', MVC_PARAM_SEPARATOR),
			$retval
		);

		return $retval;
	}


	function get_formatted_querystring()
	{
		$retval = '/'.$this->object->get_router()->get_querystring();
		$retval = str_replace(
			array('&', '='),
			array('/', MVC_PARAM_SEPARATOR),
			$retval
		);

		return $retval;
	}
}

class C_Routing_App extends C_Component
{
    static $_instances		= array();
	var    $_request_uri	= FALSE;

    function define($context= FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Routing_App');
    }

    static function &get_instance($context = False)
    {
        if (!isset(self::$_instances[$context])) {
            self::$_instances[$context] = new C_Routing_App($context);
        }
        return self::$_instances[$context];
    }
}