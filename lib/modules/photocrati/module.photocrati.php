<?php

/***
	{
		Module: photocrati-gallery-base,
                Depends: { photocrati-mvc, photocrati-active-record }
	}
***/

class Mixin_Substitute_Placeholders extends Mixin
{   
    /**
     * Removes any queued scripts that the original NextGen legacy plugin
     * provides, as each gallery type should queue using Resource Loader
     * @global type $wp_scripts 
     */
    function dequeue_scripts()
    {
        if (!is_admin()) {
            global $wp_scripts;
            
            $wp_scripts->remove('ngg_slideshow');
            $wp_scripts->remove('jquery-cycle');
        }
    }
    
    
    /**
     * Substitutes placeholder images with gallery instances
     * @param type $posts
     * @param type $query
     * @return type 
     */
    function load_gallery_instances($posts, $query)
    {   
        // Only load gallery instance outside of wp-admin
        if (!is_admin()) {

            // Iterate through each post and load gallery instances
            foreach ($posts as $post) {
                $this->substitute_placeholder_imgs($post);
            }
        }
        
        return $posts;
    }
   
    
    /**
     * Substitutes the gallery placeholder content with the gallery type frontend
     * view, returns a list of static resources that need to be loaded
     * @param stdClass $post
     */
    function substitute_placeholder_imgs(&$post)
    {   
        $found = FALSE;
        
        // Load html into parser
        $doc = new simple_html_dom();
        $doc->load($post->post_content);
        
        // Find all placeholder images
        $imgs = $doc->find("img[class='nggallery_stub']");
        if ($imgs) {
            $found = TRUE;
            
            // Needed to simulate that we're in the content of the post
            $GLOBALS['post'] = $post;
        }
        
        // Substitute each image for the gallery type frontent content
        foreach ($imgs as $img) {
            
            // The placeholder MUST have a gallery instance id
            if (preg_match("/gallery_instance_id=([^&]+)/", $img->src, $match)) {
                $gallery_instance_id = $match[1];
                
                // Instantiate factory
                $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
                
                // Create gallery instance
                $gallery_instance = $factory->create('gallery_instance');
                $gallery_instance = $gallery_instance->find($gallery_instance_id);
                
                // Create public view controller
                $controller = $factory->create(
                    'gallery_type_controller',
                    $gallery_instance->gallery_type,
                    $gallery_instance
                );
                
                // Clean up
                unset($factory);
                
                // Is the gallery type registered?
                if ($controller) {
                  
                    // Enqueue gallery specific styles and scripts
                    if ($controller->has_method('enqueue_scripts')) {
                      $controller->enqueue_scripts($gallery_instance);
                    }
                    if ($controller->has_method('enqueue_stylesheets')) {
                      $controller->enqueue_stylesheets($gallery_instance);
                    }

                    // Buffer controller action to get view
                    ob_start();
                    $controller->index();

                    // Remove all whitespace so that wpautop doesn't screw up the
                    // display
                    echo($img->outertext = $this->compress_html(ob_get_contents()));
                    ob_end_clean();                    
                }
                
                // The gallery type is no longe registered
                else {
                    $img->outertext = "<p class='invalid_gallery_type'>".
                        h(_($gallery_instance->gallery_type.
                        " is not a valid gallery type. Perhaps it was 
                            uninstalled?")).
                        "</p>";
                }
            }
        }

        // If gallery instances were found, then return the new HTML
        if ($found) {
            $post->post_content = (string)$doc->save();
            unset($GLOBALS['post']);
        }
    }    
    
    //  this function gets rid of tabs, line breaks, and white space
    function compress_html($html)
    {
        $html = preg_replace("/>\s+/", ">", $html);
        $html = preg_replace("/\s+</", "<", $html);
        $html = preg_replace("/<!--(?:(?!-->).)*-->/m", "", $html);
        return $html;
    }
}

class M_Photocrati extends C_Base_Module
{
    function define()
    {
        $this->add_mixin('Mixin_Substitute_Placeholders');
    }
    
    
    function initialize()
    {
        parent::initialize(
            'photocrati-gallery-base',
            'Photocrati Gallery',
            "Provides Photocrati's abstraction for NextGen Gallery",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function _register_hooks()
    {
        add_filter('posts_results', array(&$this, 'load_gallery_instances'), 100, 2);
        add_action('wp_print_scripts', array(&$this, 'dequeue_scripts'));
        remove_filter('the_content', 'wpautop');
    }
    
    
    function _register_adapters()
    {
        $this->_registry->add_adapter('I_Component_Factory', 'A_Photocrati_Factory');   
        $this->_registry->add_adapter('I_Gallery_Image',     'A_Parse_Image_Metadata', 'imported_image');
        $this->_registry->add_adapter('I_Gallery_Image',     'A_Auto_Rotate_Image', 'imported_image');
        $this->_registry->add_adapter('I_Gallery_Image',     'A_Auto_Resize_Image', 'imported_image');
        $this->_registry->add_adapter('I_Gallery_Instance',  'A_Gallery_Instance_Dimensions');
    }
    
    
    function _register_utilities()
    {
        $this->_registry->add_utility('I_Photocrati_Options','C_Photocrati_Options');
    }
}
new M_Photocrati();
