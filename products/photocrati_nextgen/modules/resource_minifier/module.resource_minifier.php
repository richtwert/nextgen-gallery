<?php
/*
{
    Module: photocrati-resource_minifier
}
*/

class M_Resource_Minifier extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-resource_minifier',
            'Resource Minifier',
            'Minifies and concatenates static resources',
            '0.1',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    /**
     * Initializes the resources array
     */
    function initialize()
    {
        parent::initialize();
        $this->initialize_resources();
    }

    /**
     * Registers necessary hooks for WordPress
     */
    function _register_hooks()
    {
        add_action('init', array(&$this, 'register_lazy_resources'));
        add_action('wp_enqueue_scripts', array(&$this, 'write_tags'), PHP_INT_MAX);
        add_action('wp_print_footer_scripts', array(&$this, 'write_footer_tags'), 1);
        add_action('admin_print_footer_scripts', array(&$this, 'write_footer_tags'), 1);
        add_action('wp_print_footer_scripts', array(&$this, 'start_lazy_loading'), PHP_INT_MAX);
        add_action('admin_print_footer_scripts', array(&$this, 'start_lazy_loading'), PHP_INT_MAX);
        add_filter('script_loader_src', array(&$this, 'append_script'), PHP_INT_MAX, 2);
        add_filter('style_loader_src', array(&$this, 'append_stylesheet'), PHP_INT_MAX, 2);
    }

    function _register_utilities()
    {
        $this->get_registry()->add_utility('I_Resource_Manager', 'C_Resource_Manager_Controller');
    }


    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Router', 'A_Resource_Minifier_Routes');
    }

    function register_lazy_resources()
    {
        // Register SidJS: http://www.diveintojavascript.com/projects/sidjs-load-javascript-and-stylesheets-on-demand
        $router = $this->get_registry()->get_utility('I_Router');
        wp_register_script(
            'sidjs',
            $router->get_static_url('resource_minifier#sidjs-0.1.js'),
            array('jquery'),
            '0.1'
        );

        wp_register_script(
            'lazy_resources',
            $router->get_static_url('resource_minifier#lazy_resources.js'),
            array('sidjs')
        );

        wp_enqueue_script('lazy_resources');
    }

    /**
     * Writes the HTML tags in the footer
     */
    function write_footer_tags()
    {
        $this->write_tags(TRUE);
    }

    /**
     * Writes the resource tags to the browser
     */
    function write_tags($in_footer=FALSE)
    {
        $this->write_resource_tags('styles',  $in_footer);
        $this->write_resource_tags('scripts', $in_footer);
    }

    /**
     * Gets the resouce map for a particular type
     * @param $resource_type
     * @return mixed|void
     */
    function get_resource_map($resource_type)
    {
        return get_option('ngg_'.$resource_type.'_map');
    }


    function initialize_resources()
    {
        $this->resources = array(
            'scripts'       =>  array(
                'static'    =>  array(),
                'dynamic'   =>  array(),
                'map'       =>  $this->get_resource_map('scripts')
            ),
            'styles'        =>  array(
                'static'    =>  array(),
                'dynamic'   =>  array(),
                'map'       =>  $this->get_resource_map('styles')
            )
        );
    }

    /**
     * Gets the list of scripts that should be minified
     */
    function write_resource_tags($resource_type, $in_footer=FALSE)
    {
        // Initialize this portion
        $router = NULL;
        $tagname = $resource_type == 'scripts' ? 'script' : 'link';
        $output_func = $resource_type == 'scripts' ? 'wp_print_scripts' : 'wp_print_styles';
        $this->initialize_resources();

        // Parse scripts for inclusion
        ob_start();
        $output_func();
        $html = ob_get_contents();
        ob_end_clean();

        // Strip out any scripts be loading by url, and outputs the rest. We
        // need to this for wp_localize_script() calls
        echo $this->strip_tags_with_urls($tagname, $html);

        // Store the map
        update_option('ngg_'.$resource_type.'_map', $this->resources[$resource_type]['map']);

        // Load the static scripts. These scripts will be concatenated and the final result will be
        // cached and never regenerated
        $this->write_tag($resource_type, 'static', $in_footer);

        // Load the dynamic scripts. These scripts will be concatenated but not cached,
        // as their content is known to change
        $this->write_tag($resource_type, 'dynamic', $in_footer);
    }

    /**
     * Writes the HTML tag for a resource tag of a particular group
     * @param $resource_type
     * @param $group
     */
    function write_tag($resource_type, $group, $in_footer=FALSE)
    {
        if (isset($this->resources[$resource_type])) {
            if (isset($this->resources[$resource_type][$group])) {
                if (empty($this->resources[$resource_type][$group])) return;
                $router     = $this->get_registry()->get_utility('I_Router');
                $handles    = $this->get_enqueued($resource_type, $group);
                $url        = $router->get_url("/{$group}/{$resource_type}", FALSE).'?load='.$handles;

                if ($resource_type == 'scripts') {
                    echo "<script type='text/javascript' src='{$url}'></script>\n";
                }
                else {
                    // If we're in the footer, we need to lazy load the stylesheet
                    if ($in_footer) {
                        echo '<script type="text/javascript">Lazy_Resources.enqueue("'.$url.'")</script>';
                    }

                    // Otherwise, we can just output a normal link tag
                    else echo "<link type='text/css' media='screen' rel='stylesheet' href='{$url}'/>\n";
                }

                $resources = &$this->resources[$resource_type];
                unset($resources[$group]);
            }
        }
    }


    /**
     * Gets a list of enqueued resources
     * @param string $resource_type
     * @param string $group
     * @return string
     */
    function get_enqueued($resource_type='scripts', $group='static')
    {
        return implode(";", $this->resources[$resource_type][$group]);
    }

    /**
     * Strips HTML tags from the given HTML content if a url is present
     * @param $tagname
     * @param $content
     * @return mixed
     */
    function strip_tags_with_urls($tagname, $content)
    {
        if (preg_match_all("/\s*<{$tagname}.*(src|href)=['\"].*(<\/{$tagname}>|\/>)\s*/mi", $content, $matches)) {
            foreach ($matches[0] as $tag) {
                $content = str_replace($tag, '', $content);
            }
        }
        return $content;
    }

    /**
     * Appends a script to the queue of resources to load
     * @param $src
     * @param $handle
     */
    function append_script($src, $handle)
    {
        $this->append_resource('scripts', $handle, $src);

        return $src;
    }


    /**
     * Appends a stylesheet to the resource queue
     * @param $tag
     * @param $handle
     * @return mixed
     */
    function append_stylesheet($src, $handle)
    {
        // Both the src passed in and the src registered aren't reliable, and
        // I'm not 100% sure why - it looks to be related to the esc_url() function.
        // It sucks, but we'll have to live with it for now.
        if (!preg_match("#http(s)?://\w+\.\w+#", $src)) {
            global $wp_styles;
            $src = $wp_styles->registered[$handle]->src;
        }

        $this->append_resource('styles', $handle, $src);

        return $src;
    }

    /**
     * Appends a resource to the queue
     * @param $resource_type
     * @param $handle
     */
    function append_resource($resource_type, $handle, $url)
    {
        // Add the handle to the appropriate group
        $resources = &$this->resources[$resource_type];
        $group = 'static';

        // Store the association between the handle and the url. Not all
        // resources are registered before they are enqueued so we need
        // to store this information
        $resources['map'][$handle] = $url;

        // Ensure that the group hasn't been embedded in
        // the name of the handle
        if (strpos($handle, '@') !== FALSE) {
            $parts  = explode('@', $handle);
            $group  = $parts[1];
        }

        // Add the handle to the appropriate group
        $resources[$group][] = $handle;
    }


    function start_lazy_loading()
    {
        echo '<script type="text/javascript">jQuery(function(){Lazy_Resources.load()});</script>';
    }

    function get_type_list()
    {
        return array(
            'A_Resource_Minifier_Routes'    =>  'adapter.resource_minifier_routes.php',
            'C_Resource_Manager_Controller' =>  'class.resource_manager_controller.php',
            'I_Resource_Manager'            =>  'interface.resource_manager.php',
            'M_Resource_Minifier'           =>  'module.resource_minifier.php'
        );
    }
}

new M_Resource_Minifier;