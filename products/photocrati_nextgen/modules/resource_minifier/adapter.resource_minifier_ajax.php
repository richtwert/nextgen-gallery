<?php

class A_Resource_Minifier_Ajax extends Mixin
{
    function minify_action()
    {
        $retval = array();

        if (($urls = $this->param('urls')) && is_array($urls)) {
            if (($resource = $this->param('resource'))) {
                $method = "_minify_{$resource}";
                if ($this->has_method($method)) $retval['js'] = $this->$method($urls);
            }
            else $retval['error'] = "No resource type specified";
        }
        else $retval['error'] = "No urls specified";

        return $retval;
    }

    function _minify_scripts($urls)
    {
        $retval = array();

        foreach ($urls as $url) {
            if (file_exists($path = str_replace(site_url().'/', ABSPATH, $url))) {
                $retval[] = file_get_contents($path);
            }
            elseif (is_array($response = wp_remote_get($url))) {
                $retval[] = $response['body'];
            }
        }

        return implode("\n", $retval);
    }
}