<?php

class Mixin_MVC_Controller_URI_Params extends Mixin
{
    public $_search_pattern = '/^((?<id>.+)--)?(ngg)?(?<name>.+)--(?<value>.+)/';

    public $_short_create_pattern = '%name%--%value%';
    public $_long_create_pattern  = '%id%--%name%--%value%';

    public $_parameters = array('global' => array(), 'prefixed' => array());

    function initialize()
    {
        $this->add_post_hook(
            'add_parameter',
            'Make Wordpress specific add_parameter() result adjustments',
            'Hook_Wordpress_URI_Params_Modifier'
        );

        $this->add_post_hook(
            'get_parameter',
            'Make Wordpress specific get_parameter() result adjustments',
            'Hook_Wordpress_URI_Params_Modifier'
        );
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
        if (is_null($uri) && empty($_SERVER['QUERY_STRING']))
            return array();

        if (is_null($uri))
            $target = $_SERVER['REQUEST_URI'];
        else
            $target = $uri;

        $string = parse_url($target);

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
            $target = $_SERVER['REQUEST_URI'];
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
            $this->_parameters['prefixed'][$tmp['id']][] = $tmp;
        }
        else {
            $this->_parameters['global'][] = $tmp;
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

        // check for global parameters
        foreach ($this->_parameters['global'] as $parameter) {
            if ($parameter['name'] == $name
                ||  'ngg' . $parameter['name'] == $name
                ||  $parameter['name'] == 'ngg' . $name)
            {
                $retval = $parameter['value'];
            }
        }

        // check for prefixed parameters
        if ($prefix && isset($this->_parameters['prefixed'][$prefix]))
        {
            // check for the ngg prefix in both the requested and stored names
            foreach ($this->_parameters['prefixed'][$prefix] as $parameter) {
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

    /**
     * Returns the current URL modified with the requested parameters
     *
     * @param string $name
     * @param string $val
     * @param string $prefix (optional)
     * @return string
     */
    public function add_parameter($name, $val, $prefix = NULL, $uri = NULL)
    {
        $permalink_params = $this->object->get_permalink_parameters($uri);
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
                $permalink_params[] = $this->object->create_parameter_string($name, $val, $prefix);
            else
                $query_string_params[] = $this->object->create_parameter_string($name, $val, $prefix);
        }

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

}
