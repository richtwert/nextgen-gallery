<?php
/**
 {
	Module:		photocrati-attach_to_post,
	Depends:	{ photocrati-gallery_display }
 }
 */

define(
	'PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL',
	real_admin_url('/attach_to_post/preview')
);

define(
	'PHOTOCRATI_GALLERY_ATTACH_TO_POST_DISPLAY_TAB_JS_URL',
	real_admin_url('/attach_to_post/display_tab_js')
);

class M_Attach_To_Post extends C_Base_Module
{
	var $attach_to_post_route           = 'wp-admin/attach_to_post';
	var $attach_to_post_tinymce_plugin  = 'NextGEN_AttachToPost';

	/**
	 * Defines the module
	 * @param string|bool $context
	 */
    function define($context=FALSE)
    {
        parent::define(
			'photocrati-attach_to_post',
			'Attach To Post',
			'Provides the "Attach to Post" interface for displaying galleries and albums',
			'0.3',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com',
		    $context
		);
		$this->add_mixin('Mixin_MVC_Controller_Rendering');
    }

	/**
	 * Initializes the module, and sets up the required route for the Attach
	 * to Post interface
	 */
	function initialize()
	{
		parent::initialize();
		$this->_add_routes();
		$this->renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
		$this->events   = $this->get_registry()->get_utility('I_Frame_Event_Publisher', 'attach_to_post');
	}


	/**
	 * Registers routes with the MVC Router
	 */
	function _add_routes()
	{
		$router = $this->get_registry()->get_utility('I_Router');

		$router->add_route(
			__CLASS__ . '_Attach_to_Post',
			'C_Attach_to_Post_Controller',
			array('uri'=>$router->routing_pattern($this->attach_to_post_route))
		);
	}


	/**
	 * Registers requires the utilites that this module provides
	 */
	function _register_utilities()
	{
		// This utility provides a controller that renders the
		// Attach to Post interface, used to manage Displayed Galleries
		$this->get_registry()->add_utility(
			'I_Attach_To_Post_Controller',
			'C_Attach_To_Post_Controller'
		);
	}

	/**
	 * Registers the adapters that this module provides
	 */
	function _register_adapters()
	{
		// Provides AJAX actions for the Attach To Post interface
		$this->get_registry()->add_adapter(
			'I_Ajax_Controller',   'A_Attach_To_Post_Ajax'
		);
	}


	function _register_hooks()
	{
		if (is_admin()) {
			add_action(
				'admin_enqueue_scripts',
				array(&$this, 'enqueue_static_resources'),
				1
			);
		}

		// Add hook to delete displayed galleries when removed from a post
		add_action('pre_post_update', array(&$this, 'locate_stale_displayed_galleries'));
		add_action('before_delete_post', array(&$this, 'locate_stale_displayed_galleries'));
		add_action('post_updated',	array(&$this, 'cleanup_displayed_galleries'));
		add_action('after_delete_post', array(&$this, 'cleanup_displayed_galleries'));

		// Add hook to subsitute displayed gallery placeholders
		add_filter('the_content', array(&$this, 'substitute_placeholder_imgs'), 1000, 1);

		// Emit frame communication events
		add_action('ngg_created_new_gallery',	array(&$this, 'new_gallery_event'));
		add_action('ngg_added_new_image',		array(&$this, 'new_image_event'));
		add_action('ngg_add_album',				array(&$this, 'new_album_event'));
		add_action('ngg_update_album',			array(&$this, 'album_modified_event'));
		add_action('ngg_delete_album',			array(&$this, 'album_deleted_event'));
		add_action('ngg_delete_picture',		array(&$this, 'image_deleted_event'));
		add_action('ngg_delete_gallery',		array(&$this, 'gallery_deleted_event'));
	}

	/**
     * Substitutes the gallery placeholder content with the gallery type frontend
     * view, returns a list of static resources that need to be loaded
     * @param string $content
     */
    function substitute_placeholder_imgs($content)
    {
        // Load html into parser
        $doc = new simple_html_dom();
        if ($content) {
            $doc->load($content);

            // Find all placeholder images
            $imgs = $doc->find("img[class='ngg_displayed_gallery']");
            if ($imgs) {

                // Get the displayed gallery mapper
                $mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

                // Substitute each image for the gallery type frontent content
                foreach ($imgs as $img) {

                    // The placeholder MUST have a gallery instance id
                    $preview_url = preg_quote(PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL, '/');
                    if (preg_match("/{$preview_url}\?id=(\d+)/", $img->src, $match)) {

                        // Find the displayed gallery
                        $displayed_gallery_id = $match[1];
                        $displayed_gallery = $mapper->find($displayed_gallery_id, TRUE);

                        // Get the content for the displayed gallery
                        $content = '<p>'._('Invalid Displayed Gallery').'</p>';
                        if ($displayed_gallery) {
                            $content = $this->renderer->render_displayed_gallery($displayed_gallery, TRUE);
                        }

                        // Replace the placeholder with the displayed gallery content
                        $img->outertext = $this->compress_html($content);
                    }
                }
                $content = (string)$doc->save();
            }
            return $content;
        }
    }


    /**
	 * Removes any un-nessessary whitespace from the HTML
	 * @param string $html
	 * @return string
	 */
    function compress_html($html)
    {
        $html = preg_replace("/>\s+/", ">", $html);
        $html = preg_replace("/\s+</", "<", $html);
        $html = preg_replace("/<!--(?:(?!-->).)*-->/m", "", $html);
        return $html;
    }

	/**
	 * Enqueues static resources required by the Attach to Post interface
	 */
	function enqueue_static_resources()
	{
		// Enqueue resources needed at post/page level
		if (preg_match("/\/wp-admin\/(post|post-new)\.php$/", $_SERVER['SCRIPT_NAME'])) {
			$this->_enqueue_tinymce_resources();
		}

		elseif (isset($_REQUEST['attach_to_post']) OR
		  (isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'nggallery') !== FALSE)) {
			wp_enqueue_script('iframely', $this->static_url('iframely.js'));
			wp_enqueue_style('iframely', $this->static_url('iframely.css'));
		}
	}


	/**
	 * Enqueues resources needed by the TinyMCE editor
	 */
	function _enqueue_tinymce_resources()
	{
		// Registers our tinymce button and plugin for attaching galleries
        if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_buttons', array(&$this, 'add_attach_to_post_button'));
                add_filter('mce_external_plugins', array(&$this, 'add_attach_to_post_tinymce_plugin'));
            }
        }
	}


	/**
	 * Adds a TinyMCE button for the Attach To Post plugin
	 * @param array $buttons
	 * @returns array
	 */
	function add_attach_to_post_button($buttons)
	{
		array_push(
            $buttons,
            'separator',
            $this->attach_to_post_tinymce_plugin
        );
        return $buttons;
	}


	/**
	 * Adds the Attach To Post TinyMCE plugin
	 * @param array $plugins
	 * @return array
	 * @uses mce_external_plugins filter
	 */
	function add_attach_to_post_tinymce_plugin($plugins)
	{
		$plugins[$this->attach_to_post_tinymce_plugin] = $this->static_url(
			'ngg_attach_to_post_tinymce_plugin.js'
		);

		return $plugins;
	}


	/**
	 * Locates the ids of displayed galleries that have been
	 * removed from the post, and flags then for cleanup (deletion)
	 * @global array $displayed_galleries_to_cleanup
	 * @param int $post_id
	 */
	function locate_stale_displayed_galleries($post_id)
	{
		global $displayed_galleries_to_cleanup;
		$displayed_galleries_to_cleanup = array();
		$post = get_post($post_id);
		$preview_url = preg_quote(PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL, '/');
		if (preg_match_all("/{$preview_url}\?id=(\d+)/", html_entity_decode($post->post_content), $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$preview_url = preg_quote($match[0], '/');
				// The post was edited, and the displayed gallery placeholder was removed
				if (isset($_REQUEST['post_content']) && (!preg_match("/{$preview_url}/", $_POST['post_content']))) {
					$displayed_galleries_to_cleanup[] = intval($match[1]);
				}
				// The post was deleted
				elseif (!isset($_REQUEST['action'])) {
					$displayed_galleries_to_cleanup[] = intval($match[1]);
				}
			}
		}
	}

	/**
	 * Deletes any displayed galleries that are no longer associated with
	 * a post/page
	 * @global array $displayed_galleries_to_cleanup
	 * @param int $post_id
	 */
	function cleanup_displayed_galleries($post_id)
	{
		global $displayed_galleries_to_cleanup;
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		foreach ($displayed_galleries_to_cleanup as $id) $mapper->destroy($id);
	}


	/**
	 * Notify frames that a new gallery has been created
	 * @param int $gallery_id
	 */
	function new_gallery_event($gallery_id)
	{
		$mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
		$this->events->add_event(array(
			'event'		=>	'new_gallery',
			'gallery'	=>	$mapper->find($gallery_id)
		));
	}

	/**
	 * Notify frames that a new image has been added
	 * @param mixed $image
	 */
	function new_image_event($image)
	{
		if (isset($image['id'])) {
			$mapper = $this->get_registry()->get_utility('I_Image_Mapper');
			$this->events->add_event(array(
				'event'	=>	'new_image',
				'image'	=>	$mapper->find($image['id']),
			));
		}
	}

	/**
	 * Notifies frames that a new album has been added
	 * @param int $album_id
	 */
	function new_album_event($album_id)
	{
		$mapper = $this->get_registry()->get_utility('I_Album_Mapper');
		$this->events->add_event(array(
			'event'		=>	'new_album',
			'album'		=>	$mapper->find($album_id)
		));
	}

	/**
	 * Notifies frames that an album has been modified
	 * @param int $album_id
	 * @param array $new_data
	 */
	function album_modified_event($album_id, $new_data)
	{
		$mapper = $this->get_registry()->get_utility('I_Album_Mapper');
		$this->events->add_event(array(
			'event'		=>	'album_modified',
			'album'		=>	$mapper->find($album_id)
		));
	}

	/**
	 * Notifies frames that an album has been deleted
	 * @param int $album_id
	 */
	function album_deleted_event($album_id)
	{
		$this->events->add_event(array(
			'event'		=>	'album_deleted',
			'album_id'	=>	$album_id
		));
	}

	/**
	 * Notifies frames that an image has been deleted
	 * @param int $image_id
	 */
	function image_deleted_event($image_id)
	{
		$this->events->add_event(array(
			'event'		=>	'image_deleted',
			'image_id'	=>	$image_id
		));
	}

	/**
	 * Notifies frames that a gallery has been deleted
	 * @param int $gallery_id
	 */
	function gallery_deleted_event($gallery_id)
	{
		$this->events->add_event(array(
			'event'		=>	'gallery_deleted',
			'gallery_id'=>	$gallery_id
		));
	}
}

new M_Attach_To_Post();