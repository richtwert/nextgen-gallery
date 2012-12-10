<?php

class Mixin_MVC_Controller_URI_Params extends Mixin
{
    public $_pattern = '/^(?<id>.+)--(?<name>.+)--(?<value>.+)/';
    public $_parameters = array();

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
        if (empty($_SERVER['QUERY_STRING']))
            return;

        $string = parse_url($_SERVER['REQUEST_URI']);
        $segments = explode('&', $string['query']);

        if (empty($segments))
            return;

        foreach ($segments as $segment) {
            $this->object->cache_parameter($segment);
        }
    }

    /**
     * Scans for and caches permalink style parameters (/key-val/key2-val2/)
     */
    public function cache_permalink_parameters()
    {
        $string = parse_url($_SERVER['REQUEST_URI']);
        $segments = explode('/', trim($string['path'], '/'));

        if (empty($segments))
            return;

        foreach ($segments as $segment) {
            $this->object->cache_parameter($segment);
        }
    }

    /**
     * Caches a single parameter
     *
     * @param string $parameter
     */
    public function cache_parameter($parameter)
    {
        $tmp = $this->object->is_segment_a_parameter($parameter);
        if (!empty($tmp))
            $this->_parameters[$tmp['id']][] = $tmp;
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
        preg_match($this->_pattern, $string, $matches);

        if (7 == count($matches))
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
     * @param string $prefix
     * @return mixed
     */
    public function get_parameter($name, $prefix)
    {
        $retval = NULL;

        if (isset($this->_parameters[$prefix]))
        {
            foreach ($this->_parameters[$prefix] as $parameter) {
                if ($parameter['name'] == $name)
                    $retval = $parameter['value'];
            }
        }

        return $retval;
    }

}
