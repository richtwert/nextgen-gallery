<?php
class C_Resource_Manager_Controller extends C_MVC_Controller
{
    var $_fs            = NULL;
    var $_document_root = NULL;
    var $_resource_map  = NULL;

    /**
     * Gets an instance of the controller
     * @var array
     * @return C_Resource_Manager_Controller
     */
    static $_instances = array();
    static function get_instance($context)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }


    /**
     * Defines the object
     * @param bool $context
     */
    function define($context=FALSE)
    {
        parent::define($context);
        $this->implement('I_Resource_Manager');
    }

    /**
     * Gets the FS utility
     * @return C_Component|null
     */
    function _get_fs_utility()
    {
        if (is_null($this->_fs))
            $this->_fs = $this->get_registry()->get_utility('I_Fs');

        return $this->_fs;
    }

    /**
     * Gets a cached map association between handles and urls
     * @param $resource_type
     * @return mixed|void
     */
    function _get_resource_map($resource_type)
    {
        if (is_null($this->_resource_map))
            $this->_resource_map = get_option('ngg_'.$resource_type.'_map');

        return $this->_resource_map;
    }

    /**
     * Gets the url associated with a script/stylesheet handle
     * @param $handle
     * @param $resource_type: 'scripts' or 'styles'
     * @return string|FALSE
     */
    function _get_handle_url($handle, $resource_type)
    {
        global $wp_scripts, $wp_styles;
        $register = $resource_type == 'scripts' ? $wp_scripts : $wp_styles;
        $retval = FALSE;

        // First, look for the handle in the list of
        // registered scripts
        if (isset($register->registered[$handle])) {
            $retval = $register->registered[$handle]->src;
        }

        // If not available, we'll look up the url from our
        // cache
        else {
            $map = $this->_get_resource_map($resource_type);
            if (isset($map[$handle])) $retval = $map[$handle];
        }

        return $retval;
    }

    /**
     * Gets the source of a script/stylesheet
     * @param $url
     * @return string
     */
    function _get_source($url)
    {
        $retval     = '';
        $fs         = $this->_get_fs_utility();
        $docroot    = $fs->get_document_root();
        $http_site  = $this->get_router()->get_base_url();
        $https_site = str_replace('http://', 'https://', $http_site);
        $path       = FALSE;

        // Is this a local file?
        if (strpos($url, '/') === 0) {
            $path = $fs->join_paths($docroot, $url);
        }

        // This is a real url. Is it local?
        elseif (strpos($url, $http_site) !== FALSE) {
            $path = str_replace($http_site, '', $url);
            $path = $fs->join_paths($docroot, $path);
        }

        // This is a real url. Is it local and using HTTPS?
        elseif (strpos($url, $https_site) !== FALSE) {
            $path = str_replace($https_site, '', $url);
            $path = $fs->join_paths($docroot, $path);
        }

        // This is a real url and it's not local. We'll have to fetch it
        else {
            $retval = wp_remote_fopen($url);
        }

        // If a path has been set, and it's exists on the filesystem
        if ($path && file_exists($path)) {
            $retval = file_get_contents($path);
        }

        // This a local but dynamically generated resource. We need
        // to fetch it using HTTP
        else {
            if (strpos($url, '/') === 0) {
                $url = $this->get_router()->get_url($url);
            }
            $retval = wp_remote_fopen($url);
        }

        return $retval;
    }

    /**
     * Concatenates the resources requested
     * @param $resource_type
     * @return string
     */
    function _concatenate_resources($resource_type)
    {
        $retval = array();

        // Include each script
        if (($handles = $this->param('load'))) {
            foreach (explode(';', $handles) as $handle) {
                if (($src = $this->_get_handle_url($handle, $resource_type))) {
                    $retval[] = $this->_get_source($src);
                }
            }
        }
        return implode("\n", $retval);
    }


    function static_scripts_action()
    {
        $this->set_content_type('javascript');
        $this->expires("+1 hour");
        $this->render();
        echo $this->_concatenate_resources('scripts');
    }

    function dynamic_scripts_action()
    {
        $this->set_content_type('javascript');
        $this->do_not_cache();
        $this->render();
        echo $this->_concatenate_resources('scripts');
    }

    function static_styles_action()
    {
        $this->set_content_type('css');
        $this->expires("+1 hour");
        $this->render();
        echo $this->_concatenate_resources('styles');
    }

    function dynamic_styles_action()
    {
        $this->set_content_type('css');
        $this->do_not_cache();
        $this->render();
        echo $this->_concatenate_resources('styles');
    }
}