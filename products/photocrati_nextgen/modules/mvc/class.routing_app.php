<?php

class Mixin_Routing_App extends Mixin
{
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

    function rewrite($src, $dst, $redirect = FALSE)
    {
        // ensure that rewrite patterns array exists
        if (!is_array($this->object->_rewrite_patterns))
            $this->object->_rewrite_patterns = array();

        // fetch all rewrite patterns
        $patterns = $this->object->_rewrite_patterns;

        // add the rewrite pattern
        $patterns[$this->object->_route_to_regex($src)] = array('dst' => $dst, $redirect => $redirect);

        // update rewrite patterns;
        $this->object->_rewrite_patterns = $patterns;
    }

    function serve_request($request_uri = FALSE)
    {
        $served = FALSE;

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
            foreach ($this->object->_routing_patterns as $pattern => $details) {
                if (preg_match_all($pattern, $request_uri, $matches, PREG_SET_ORDER))
                {
                    $action = $details['action'] . '_action';
                    $controller = new $details['controller']($details['context']);
                    $controller->$action();
                    throw new E_Clean_Exit();
                }
            }

            // start rewriting urls
            foreach ($this->object->_rewrite_patterns as $pattern => $details) {

                var_dump($pattern, $request_uri);

                if (preg_match_all($pattern, $request_uri, $matches, PREG_SET_ORDER))
                {
                    // perform substitutions
                    foreach ($matches as $match) {
                        foreach ($match as $key => $val) {
                            if (is_numeric($key))
                                continue;
                            $dst = str_replace("{{$key}}", $val, $details['dst']);
                        }
                    }

                    var_dump(
                        $dst,
                        $matches
                    );

                    $parsed_url = str_replace($matches[0], $dst, $request_uri);
                    var_dump($parsed_url);

                    // strip $matches[0] from $request_uri
                    $url_without_routed_parameters = str_replace($matches[0], '', $request_uri);
                    $_SERVER['REQUEST_URI'] = $url_without_routed_parameters;

                    var_dump($_SERVER['REQUEST_URI']);
                    exit;

                    // redirect now if we're to do so...
                    if (isset($details['redirect']))
                    {
                        switch (intval($details['redirect']))
                        {
                            case 301:
                                header("HTTP/1.1 301 Moved Permanently");
                            case 302:
                            case 1:
                                header("Location: {need way to get app url}");
                                break;
                        }
                        throw new E_Clean_Exit();
                    }
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
}

class C_Routing_App extends C_Component
{
    static $_instances = array();

    function define($context = FALSE)
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