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
     * Registers necessary hooks for WordPress
     */
    function _register_hooks()
    {
        add_action('wp_print_footer_scripts', array(&$this, 'minify_resources'), 1);
    }

    /**
     * Registers needed component adapters
     */
    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Ajax_Controller', 'A_Resource_Minifier_Ajax');
    }


    /**
     * Generate a script which will minify the static resources loaded
     * in the footer
     */
    function minify_resources()
    {
        echo "<!-- Resource Minifier Starts -->";
        $urls = $this->get_script_urls();
        include('templates/script.php');
        echo "<!-- Resource Minifier Ends -->";
    }


    /**
     * Gets the list of scripts that should be minified
     */
    function get_script_urls()
    {
        $retval = array();
        ob_start();
        wp_print_scripts();
        $html = ob_get_contents();
        ob_end_clean();

        if (preg_match_all("/src=['\"]([^'\"]+)/", $html, $matches)) {
            return $matches[1];
        }
        return $retval;
    }
}

new M_Resource_Minifier;