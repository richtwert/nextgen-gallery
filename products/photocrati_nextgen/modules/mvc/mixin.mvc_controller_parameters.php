<?php

class Mixin_MVC_Controller_Parameters extends Mixin
{
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

        $router = $this->object->get_registry()->get_utility('I_Router');
        $current_application = $router->get_routed_app();

        $global_parameters = $current_application->get_global_parameters();
        $prefixed_parameters = $current_application->get_prefixed_parameters();

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
        $router = $this->object->get_registry()->get_utility('I_Router');
        $current_application = $router->get_routed_app();

        $permalink_params = $current_application->get_permalink_parameters($uri);
        $query_string_params = $current_application->get_query_string_parameters($uri);

        $found = FALSE;

        // check for modifications in the permalink parameters
        foreach ($permalink_params as &$parameter) {
            $tmp = $current_application->modify_parameter_string($parameter, $name, $val, $prefix);
            if ($tmp)
            {
                $found = TRUE;
                $parameter = $tmp;
            }
        }

        // check for modifications in the query string parameters
        foreach ($query_string_params as &$parameter) {
            $tmp = $current_application->modify_parameter_string($parameter, $name, $val, $prefix);
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

        $final_string = $current_application->assemble_final_string($permalink_params, $query_string_params);

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
        $router = $this->object->get_registry()->get_utility('I_Router');
        $current_application = $router->get_routed_app();

        $permalink_params    = $current_application->get_permalink_parameters($uri);
        $query_string_params = $current_application->get_query_string_parameters($uri);

        foreach ($permalink_params as $key => &$parameter) {
            $tmp = $current_application->is_segment_a_parameter($parameter);
            if (!$tmp)
                continue;
            if (!$prefix && $tmp['name'] == $name && $tmp['id'] == '')
                unset($query_string_params[$key]);
            if ($prefix && $tmp['name'] == $name && $tmp['id'] == $prefix)
                unset($query_string_params[$key]);
        }

        foreach ($query_string_params as $key => &$parameter) {
            $tmp = $current_application->is_segment_a_parameter($parameter);
            if (!$tmp)
                continue;
            if (!$prefix && $tmp['name'] == $name && $tmp['id'] == '')
                unset($query_string_params[$key]);
            if ($prefix && $tmp['name'] == $name && $tmp['id'] == $prefix)
                unset($query_string_params[$key]);
        }

        $final_string = $current_application->assemble_final_string($permalink_params, $query_string_params);

        return $final_string;
    }
}
