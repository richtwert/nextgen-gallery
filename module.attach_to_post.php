<?php

/***
	{
		Module: photocrati-attach_to_post,
		Depends: { photocrati-admin }
	}
***/

define(
    'PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_ROUTING_PATTERN', 
    '/\/wp-admin\/attach_to_post\/?([^\?]*)/'
);

define(
    'PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_TINYCE_PLUGIN',
    'NextGen_AttachToPost'
);


define('PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_URL', path_join(
    PHOTOCRATI_GALLERY_MODULE_URL,
    basename(dirname(__FILE__))
));

define('PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_AJAX_URL', admin_url('/attach_to_post/ajax'));

class M_Attach_to_Post extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-attach_to_post',
            'Photocrati Attach to Post/Page Interface',
            'Provides an easy-to-use interface for attaching new or existing galleries to any post type',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
        
        $this->add_mixin('Mixin_MVC_Controller_Rendering');
        $this->add_mixin('Mixin_Substitute_Gallery_Placeholders');
    }
    
    
    function initialize()
    {
        @ini_set('post_max_size', '20M');
        @ini_set('upload_max_filesize', '20M');
        @ini_set('max_input_time', '1600');
        
        $this->_register_routes();
    }
    
    
    function _register_routes()
    {
        $router = $this->_get_registry()->get_singleton_utility('I_Router');
        $router->add_route(__CLASS__, 'C_Attach_to_Post', array(
            'uri'=>PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_ROUTING_PATTERN
        ));
    }
    
    
    function _register_adapters()
    {
        $this->_get_registry()->add_adapter('I_Component_Factory', 'A_Attached_Gallery_Factory');
        $this->_get_registry()->add_adapter('I_Attached_Gallery',  'A_Attached_Gallery_Dimensions');
    }
    
    
    function _register_hooks()
    {
        add_action('admin_enqueue_scripts', array(&$this, 'load_tinymce_helpers'));
        
        // Add custom post type for attached galleries
        register_post_type('attached_gallery', array(
            'labels'            =>  array(
                'name'          =>  _('Attached Galleries'),
                'singular_name' =>  _('Attached Gallery'),
                'add_new'       =>  _('Add New'),
                'add_new_item'  =>  _('Attach Gallery'),
            ),
            'public'            =>  FALSE,
            'publicly_queryable' =>  FALSE,
            'show_ui'           =>  FALSE,
            'query_var'         =>  FALSE,
            'capabilitiy_post'  =>  'post',
            'hierarchical'      =>  FALSE,
            'supports'          =>  array('title')
        ));
        
        register_post_type('attached_gal_image', array(
            'labels'            =>  array(
                'name'          =>  _('Attached Gallery Images'),
                'singular_name' =>  _('Attached Gallery Image'),
                'add_new'       =>  _('Add New'),
                'add_new_item'  =>  _('New Attached Gallery Image'),
            ),
            'public'            =>  FALSE,
            'publicly_queryable' =>  FALSE,
            'show_ui'           =>  FALSE,
            'query_var'         =>  FALSE,
            'capabilitiy_post'  =>  'post',
            'hierarchical'      =>  FALSE,
            'supports'          =>  array('title')
        ));
        
        // Add hooks to load attached galleries
        add_filter('posts_results',     array(&$this, 'load_attached_galleries'), 100, 2);
        remove_filter('the_content',    'wpautop');
    }
    
    
    function load_tinymce_helpers()
    {
        global $post_ID;
        
        // Registers our tinymce button and plugin for attaching galleries
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_buttons', array(&$this, 'add_tinymce_button'));
                add_filter('mce_external_plugins', array(&$this, 'add_tinymce_plugin'));
            }
        }

        // Enqueue the tinymce helpers script
        wp_register_script('tinymce_helpers', $this->static_url('tinymce_helpers.js'));
        wp_enqueue_script('tinymce_helpers');
        wp_localize_script('tinymce_helpers', 'vars', array(
           'preview_url'    =>  admin_url('attach_to_post/preview'),
           'post_id'        =>  $post_ID
        ));
    }
    
    
    /**
     * Integrates with the WordPress framework to add a tinymce button
     * for attaching NextGen galleries
     * @filter: mce_buttons
     * @return array
     */
    function add_tinymce_button($buttons)
    {
        array_push(
            $buttons, 
            'separator', 
            PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_TINYCE_PLUGIN
        );
        return $buttons;
    }
    
    
    /**
     * Adds our TinyMCE plugin used to attach galleries to posts/pages
     * @filter: mce_external_plugins
     * @param array $plugins
     * @return array 
     */
    function add_tinymce_plugin($plugins)
    {
        $plugins[PHOTOCRATI_GALLERY_MOD_ATTACH_TO_POST_TINYCE_PLUGIN] = $this->static_url(
          'nextgen_attach_to_post.js'
        );
        return $plugins;
    }
}

new M_Attach_to_Post();
