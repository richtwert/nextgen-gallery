<?php

class Mixin_Routing_App_Parameters extends Mixin
{
    public $_search_pattern = '/^((?<id>.+)--)?(ngg)?(?<name>.+)--(?<value>.+)/';

    public $_short_create_pattern = '%name%--%value%';
    public $_long_create_pattern  = '%id%--%name%--%value%';

	function initialize()
	{
		if (!isset($this->object->_parameters)) {
			$this->object->_parameters = array(
				'global' => array(), 'prefixed' => array()
			);
		}
	}


    /**
     * Starts a chain that will cache SEO/permalink style and query-string parameters into a formatted array
     */
    public function cache_all_parameters()
    {
        $this->object->cache_permalink_parameters();
        $this->object->cache_query_string_parameters();
    }

    /**
     * Scans for and caches query string parameters (?key=val&key2=val2)
     */
    public function cache_query_string_parameters()
    {
        foreach ($this->object->get_query_string_parameters() as $segment) {
            $this->object->cache_parameter($segment);
        }
    }

    /**
     * Returns available query string parameters, if any
     *
     * @return array
     */
    public function get_query_string_parameters($uri = NULL)
    {
        if (is_null($uri))
            $uri = $this->object->get_router()->context;

        $string = parse_url($uri);

        if (empty($string['query']))
            return array();

        $segments = explode('&', $string['query']);

        if (empty($segments))
            return array();

        if ($segments == array(0 => ''))
            return array();

        return $segments;
    }

    /**
     * Scans for and caches permalink style parameters (/key-val/key2-val2/)
     */
    public function cache_permalink_parameters()
    {
        foreach ($this->object->get_permalink_parameters() as $segment) {
            $this->object->cache_parameter($segment);
        }
    }

    /**
     * Returns available permalink parameters, if any
     *
     * @return array
     */
    public function get_permalink_parameters($uri = NULL)
    {
        if (is_null($uri))
            $target = $this->object->get_router()->get_request_uri();
        else
            $target = $uri;

        $string = parse_url($target);

        if (empty($string['path']))
            return array();

        $segments = explode('/', trim($string['path'], '/'));

        if (empty($segments))
            return array();

        if ($segments == array(0 => ''))
            return array();

        return $segments;
    }

    /**
     * Returns the cached global parameters
     *
     * @return array
     */
    public function get_global_parameters()
    {
        return $this->object->_parameters['global'];
    }

    /**
     * Returns the cached prefixed parameters
     *
     * @return array
     */
    public function get_prefixed_parameters()
    {
        return $this->object->_parameters['prefixed'];
    }

    /**
     * Caches a single parameter
     *
     * @param string $parameter
     */
    public function cache_parameter($parameter)
    {
        $tmp = $this->object->is_segment_a_parameter($parameter);

        if (empty($tmp))
            return;

        if (!empty($tmp['id']))
        {
            $this->object->_parameters['prefixed'][$tmp['id']][] = $tmp;
        }
        else {
            $this->object->_parameters['global'][] = $tmp;
        }
    }

    /**
     * Determines if a string segment is a parameter; returns a formatted array on success and FALSE on failure
     *
     * @param string $string
     * @return array|bool Formatted array if the segment is a parameter; FALSE otherwise.
     */
    public function is_segment_a_parameter($string)
    {
        $matches = array();
        preg_match($this->_search_pattern, $string, $matches);

        if (9 == count($matches))
        {
            return array('id'     => $matches['id'],
                         'name'   => $matches['name'],
                         'value'  => $matches['value'],
                         'source' => $string);
        }
        else {
            return FALSE;
        }
    }

    /**
     * Given a name/value pair this creates a parameter string to be added/appended/etc
     *
     * @param string $name
     * @param string $val
     * @param string $prefix (optional)
     * @return string
     */
    public function create_parameter_string($name, $val, $prefix = NULL)
    {
        if (!empty($prefix))
            $string = $this->_long_create_pattern;
        else
            $string = $this->_short_create_pattern;

        if (TRUE === $val)
            $val = 'true';

        if (FALSE === $val)
            $val = 'false';

        if (NULL === $val)
            $val = 'null';

        $string = str_replace('%id%',    $prefix, $string);
        $string = str_replace('%name%',  $name,   $string);
        $string = str_replace('%value%', $val,    $string);

        return $string;
    }

    /**
     * Compares $parameter to the name/value/prefix pairing and returns a modified string or FALSE
     *
     * @param string $parameter
     * @param string $name
     * @param string $val
     * @param string $prefix
     * @return mixed
     */
    public function modify_parameter_string($parameter, $name, $val, $prefix = NULL)
    {
        $parsed = $this->object->is_segment_a_parameter($parameter);
        if (!$parsed)
            return FALSE;

        $string = FALSE;

        if (empty($prefix) && empty($parsed['id']) && $name == $parsed['name'])
            $string = $this->object->create_parameter_string($name, $val);

        if (!empty($prefix) && $prefix == $parsed['id'] && $name == $parsed['name'])
            $string = $this->object->create_parameter_string($name, $val, $prefix);

        return $string;
    }

    public function assemble_final_string($permalink_params, $query_string_params)
    {
        $final_string = '';

        if (!empty($permalink_params))
        {
            $permalink_string = '/' . implode('/', $permalink_params) . '/';
            $final_string .= $permalink_string;
        }

        if (!empty($query_string_params))
        {
            $query_string_string = '?' . implode('&', $query_string_params);
            $final_string .= $query_string_string;
        }

        return $final_string;
    }

    /**
     * Finds and returns a cached parameter
     *
     * @param string $name
     * @param string $prefix (optional)
     * @return mixed
     */
    public function get_parameter($name, $prefix = NULL)
    {
        // it's important that we check these in order; never terminate before the end of the function here
        $retval = NULL;

        // check $_GET first
        if (!empty($_REQUEST[$name]))
            $retval = $_REQUEST[$name];

        if (!empty($_REQUEST['ngg' . $name]))
            $retval = $_REQUEST['ngg' . $name];

        $global_parameters   = $this->object->get_global_parameters();
        $prefixed_parameters = $this->object->get_prefixed_parameters();

        // check for global parameters
        foreach ($global_parameters as $parameter) {
            if ($parameter['name'] == $name
                ||  'ngg' . $parameter['name'] == $name
                ||  $parameter['name'] == 'ngg' . $name)
            {
                $retval = $parameter['value'];
            }
        }

        // check for prefixed parameters
        if ($prefix && isset($prefixed_parameters[$prefix]))
        {
            // check for the ngg prefix in both the requested and stored names
            foreach ($prefixed_parameters[$prefix] as $parameter) {
                if ($parameter['name'] == $name
                    ||  'ngg' . $parameter['name'] == $name
                    ||  $parameter['name'] == 'ngg' . $name)
                {
                    $retval = $parameter['value'];
                }
            }
        }

        if (is_string($retval))
        {
            if ('null' == strtolower($retval))
                $retval = NULL;
            if ('false' == strtolower($retval))
                $retval = FALSE;
            if ('true' == strtolower($retval))
                $retval = TRUE;
        }

        return $retval;
    }

    /**
     * Adds an URL parameter to the requested URL. If a variable of the same name already exists it's value will be
     * updated in-place.
     *
     * @param string $name
     * @param string $val
     * @param string $prefix (optional)
     * @param string $uri (optional)
     * @return string
     */
    public function add_parameter($name, $val, $prefix = NULL, $uri = NULL)
    {
        $permalink_params    = $this->object->get_permalink_parameters($uri);
        $query_string_params = $this->object->get_query_string_parameters($uri);

        $found = FALSE;

        // check for modifications in the permalink parameters
        foreach ($permalink_params as &$parameter) {
            $tmp = $this->object->modify_parameter_string($parameter, $name, $val, $prefix);
            if ($tmp)
            {
                $found = TRUE;
                $parameter = $tmp;
            }
        }

        // check for modifications in the query string parameters
        foreach ($query_string_params as &$parameter) {
            $tmp = $this->object->modify_parameter_string($parameter, $name, $val, $prefix);
            if ($tmp)
            {
                $found = TRUE;
                $parameter = $tmp;
            }
        }

        // we didn't modify any existing parameters, so add it
        if (!$found)
        {
            $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

            if ($settings->usePermalinks)
                $permalink_params[] = $current_application->create_parameter_string($name, $val, $prefix);
            else
                $query_string_params[] = $current_application->create_parameter_string($name, $val, $prefix);
        }

        $final_string = $this->object->assemble_final_string($permalink_params, $query_string_params);

        return $final_string;
    }

    /**
     * Removes an URL parameter from the requested URL. This will not remove prefixed variables unless their
     * prefix is part of the request, nor will it remove global variables if a prefix has been supplied.
     *
     * @param string $name
     * @param string $val
     * @param string $prefix (optional)
     * @param string $uri (optional)
     * @return string
     */
    public function del_parameter($name, $prefix = NULL, $uri = NULL)
    {
        $permalink_params    = $this->object->get_permalink_parameters($uri);
        $query_string_params = $this->object->get_query_string_parameters($uri);

        foreach ($permalink_params as $key => &$parameter) {
            $tmp = $this->object->is_segment_a_parameter($parameter);
            if (!$tmp)
                continue;
            if (!$prefix && $tmp['name'] == $name && $tmp['id'] == '')
                unset($query_string_params[$key]);
            if ($prefix && $tmp['name'] == $name && $tmp['id'] == $prefix)
                unset($query_string_params[$key]);
        }

        foreach ($query_string_params as $key => &$parameter) {
            $tmp = $this->object->is_segment_a_parameter($parameter);
            if (!$tmp)
                continue;
            if (!$prefix && $tmp['name'] == $name && $tmp['id'] == '')
                unset($query_string_params[$key]);
            if ($prefix && $tmp['name'] == $name && $tmp['id'] == $prefix)
                unset($query_string_params[$key]);
        }

        $final_string = $this->object->assemble_final_string($permalink_params, $query_string_params);

        return $final_string;
    }

}