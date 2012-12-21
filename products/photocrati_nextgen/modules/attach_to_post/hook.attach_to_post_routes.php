<?php

class Hook_Attach_To_Post_Routes extends Hook
{
    /**
     * Registers Attach-To-Post routes
     */
    function add_routes()
    {
        $router   = $this->object->get_registry()->get_utility('I_Router');
        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        $original_permalinks_setting = $settings->usePermalinks;
        $settings->usePermalinks = FALSE;

        $url = $this->get_registry()
                    ->get_utility('I_Display_Type_Controller')
                    ->add_parameter('attach_to_post', TRUE, NULL, admin_url());

        define('NEXTGEN_GALLERY_ATTACH_TO_POST_URL', $url);

        define(
        'NEXTGEN_GALLERY_ATTACH_TO_POST_PREVIEW_URL',
            NEXTGEN_GALLERY_ATTACH_TO_POST_URL . '/preview'
        );

        define(
        'NEXTGEN_GALLERY_ATTACH_TO_POST_DISPLAY_TAB_JS_URL',
            NEXTGEN_GALLERY_ATTACH_TO_POST_URL . '/display_tab_js'
        );

        $app = $router->create_app();
        $app->route(
            array($url),
            array(
                'controller' => 'C_Attach_to_Post_Controller',
                'action'  => 'index',
                'context' => FALSE,
                'method'  => array('GET')
            )
        );

        $settings->usePermalinks = $original_permalinks_setting;
    }
}