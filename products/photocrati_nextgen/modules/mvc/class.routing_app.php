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
     * Determines if the current routing app meets our requirements and serves them
     *
     * @param string $request_uri (optional)
     * @return bool
     * @throws E_Clean_Exit
     */
    function serve_request($request_uri = FALSE)
    {
        $served = FALSE;

        $this->object->cache_all_parameters($request_uri);

        // ensure that the routing patterns array exists
        if (!is_array($this->object->_routing_patterns))
            $this->object->_routing_patterns = array();

        // ensure that rewrite patterns array exists
        if (!is_array($this->object->_rewrite_patterns))
            $this->object->_rewrite_patterns = array();

        // get the request uri to match
        if (!$request_uri)
            $request_uri = $this->object->get_router()->get_request_uri();

        // if the application root matches, then we'll try to route the request
        if (preg_match(preg_quote('#' . $this->object->context . '#i'), $request_uri))
        {
            // start rewriting urls
            foreach ($this->object->_rewrite_patterns as $pattern => $details) {

                if (preg_match_all($pattern, $request_uri, $matches, PREG_SET_ORDER))
                {
                    // strip $matches[0] from $request_uri and rebuild the url without our parameters and with
                    // the parameters having been substituted
                    $url_without_routed_parameters = str_replace($matches[0], '', $request_uri);
                    $url_with_routed_parameters = $url_without_routed_parameters;

                    if (substr($url_without_routed_parameters, -1) != '/')
                        $url_without_routed_parameters .= '/';

                    // perform substitutions
                    foreach ($matches as $match) {
                        foreach ($match as $key => $val) {
                            if (is_numeric($key))
                                continue;
                            $dst = str_replace("{{$key}}", $val, $details['dst']);
                            $url_with_routed_parameters .= $dst;
                        }
                    }

                    $served = TRUE;

                    // redirect if we're to do so
                    if ($details['redirect'])
                    {
                        switch (intval($details['redirect']))
                        {
                            case 1:
                            case 301:
                                header("HTTP/1.1 301 Moved Permanently");
                                break;
                            case 302:
                                header("HTTP/1.1 302 302 Found");
                                break;
                        }
                        header("Location: {$url_with_routed_parameters}");
                    }
                }
            }

            // finally handle routed endpoints
            foreach ($this->object->_routing_patterns as $pattern => $details) {
                if (preg_match_all($pattern, $request_uri, $matches, PREG_SET_ORDER))
                {
                    $action = $details['action'] . '_action';
                    $controller = new $details['controller']($details['context']);
                    $controller->$action();
                    throw new E_Clean_Exit();
                }
            }
        }

        return $served;
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
        $regex_pattern = preg_quote(
            str_replace(
                array('{', '}'),
                array('~', '~'),
                $route
            )
        );

        $regex_pattern = '#' . $regex_pattern . '#i';

        // convert placeholders to regex as well
        return preg_replace('/~([^~]+)~/', '(?<\1>[^\/]+)', $regex_pattern);
    }

    public function get_router()
    {
        return $this->_router;
    }
}

class C_Routing_App extends C_Component
{
    static $_instances = array();
    public $_router = FALSE;

    function define($context = FALSE, $router = FALSE)
    {
        parent::define($context, $router);

        $this->_router = $router;

        $this->add_mixin('Mixin_Routing_App');
        $this->add_mixin('Mixin_Routing_App_Parameters');
    }

    static function &get_instance($context = False)
    {
        if (!isset(self::$_instances[$context])) {
            self::$_instances[$context] = new C_Routing_App($context);
        }
        return self::$_instances[$context];
    }
}