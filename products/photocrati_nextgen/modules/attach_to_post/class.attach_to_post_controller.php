<?php

/**
 * Provides the ability to create and edit C_Displayed_Galleries
 */
class C_Attach_To_Post_Controller extends C_NextGen_Backend_Controller
{
	static $_instances = array();
	var $_displayed_gallery;


	/**
	 * Gets an instance of the controller
	 * @param string $context
	 * @return C_NextGen_Settings_Controller
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = function_exists('get_called_class') ?
				get_called_class() : get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	/**
	 * Defines what instance methods the Attach To Post Controller has
	 * @param mixed $context
	 */
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Attach_To_Post_Controller');
		$this->add_mixin('Mixin_Attach_To_Post_Display_Tab');
		$this->implement('I_Attach_To_Post_Controller');
	}
}

/**
 * Provide instance methods for the Attach To Post Controller
 */
class Mixin_Attach_To_Post_Controller extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'enqueue_backend_resources',
			'Enqueues resources needed for the Attach to Post interface',
			__CLASS__,
			'enqueue_attach_to_post_resources'
		);

	}


	function enqueue_attach_to_post_resources()
	{
		// Enqueue frame event publishing
		do_action('admin_enqueue_scripts');
		wp_enqueue_script('frame_event_publisher');

		// Enqueue JQuery UI libraries
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');

		// Enqueue chosen, a library to make our drop-downs look pretty
//		wp_enqueue_style('chosen', $this->static_url('chosen.css'));
//		wp_enqueue_script(
//			'chosen', $this->static_url('chosen.js'), array('jquery')
//		);
		wp_enqueue_style('select2', $this->static_url('select2.css'));
		wp_enqueue_script('select2', $this->static_url('select2.js'));

		// Ensure we have the AJAX module ready
		wp_enqueue_script('photocrati_ajax', PHOTOCRATI_GALLERY_AJAX_URL.'/js');

		// Enqueue logic for the Attach to Post interface as a whole
		wp_enqueue_script(
			'ngg_attach_to_post', $this->static_url('attach_to_post.js')
		);
		wp_enqueue_style(
			'ngg_attach_to_post', $this->static_url('attach_to_post.css')
		);

		// Enqueue our JS templating library, Handlebars
		wp_enqueue_script(
			'handlebars',
			$this->static_url('handlebars-1.0.0.beta.6.js'),
			array(),
			'1.0.0b6'
		);

		// Enqueue the underscore.js library, required by Backbone
		wp_enqueue_script(
			'underscore',
			$this->static_url('underscore.js'),
			array(),
			'1.4.2'
		);

		// Enqueue backbone.js library, required by the Attach to Post display tab
		wp_enqueue_script(
			'backbone',
			$this->static_url('backbone.js'),
			array('jquery', 'underscore'),
			'0.9.2'
		);

		wp_enqueue_script(
			'underscore.string',
			$this->static_url('underscore.string.js'),
			array('underscore'),
			'2.3.0'
		);

		// Enqueue the backbone app for the display tab
		wp_enqueue_script(
			'ngg_display_tab',
			add_query_arg(
				'id',
				$this->_displayed_gallery->id(),
				PHOTOCRATI_GALLERY_ATTACH_TO_POST_DISPLAY_TAB_JS_URL
			),
			array('backbone', 'underscore.string')
		);
		wp_localize_script(
			'ngg_display_tab',
			'ngg_displayed_gallery_preview_url',
			PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL
		);
	}

	/**
	 * Renders the interface
	 */
	function index_action()
	{
		// For a valid request, we'll display our tabbed interface
		if ($this->object->_validate_request()) {

			// Enqueue resources
			$this->enqueue_backend_resources();
			$this->object->render_view('attach_to_post', array(
				'page_title'	=>	$this->object->_get_page_title(),
				'tabs'			=>	$this->object->_get_main_tabs(),
				'tab_titles'	=>	$this->object->_get_main_tab_titles()
			));
		}

		// Bad request!
		else {
			$this->object->show_error("Displayed Gallery could not found.", 404);
		}
	}

	function preview_tab_css($return=FALSE)
	{
		$settings = $this->object->get_registry()->get_utlity('I_NextGen_Settings');
		$this->object->set_content_type('css');
		$this->object->do_not_cache();
		return $this->object->render_view('preview_tab_css', array(
			// Need to determine what parameters to include
		));
	}


	/**
	 * Displays a preview image for the displayed gallery
	 */
	function preview_action()
	{
		$found_preview_pic = FALSE;

		if ($this->object->_validate_request()) {
            $dyn_thumbs		= $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
			$storage		= $this->object->get_registry()->get_utility('I_Gallery_Storage');
			$image_mapper	= $this->object->get_registry()->get_utility('I_Image_Mapper');

			// Get the first entity from the displayed gallery. We will use this
			// for a preview pic
			$entity = array_pop($this->object->_displayed_gallery->get_entities(1, FALSE, FALSE, TRUE));
			$image = FALSE;
			if ($entity) {
				// This is an album or gallery
				if (isset($entity->previewpic)) {
					$image = (int)$entity->previewpic;
					if (($image = $image_mapper->find($image))) {
							$found_preview_pic = TRUE;
					}
				}

				// Is this an image
				else if (isset($entity->galleryid)) {
					$image = $entity;
					$found_preview_pic = TRUE;
				}
			}

			// Were we able to find a preview pic? If so, then render it
			$found_preview_pic = $storage->render_image($image, $dyn_thumbs->get_size_name(array(
				'width'     =>  200,
				'height'    =>  200,
				'quality'   =>  90,
				'type'		=>	'jpg'
			), TRUE));
		}

		// Render invalid image if no preview pic is found
		if (!$found_preview_pic) {
			$filename = $this->object->find_static_file('invalid_image.png');
			$this->set_content_type('image/png');
			readfile($filename);
			$this->render();
		}
	}


	/**
	 * Validates the request and fetches the associated displayed gallery
	 * @return boolean
	 */
	function _validate_request()
	{
		$valid_request = TRUE;

		// If an ID was passed, then we need to attempt
		// retrieving the displayed gallery to edit.
		// If the displayed gallery doesn't exist, then it's an
		// invalid request
		if (($id = $this->object->param('id')) && !isset($this->object->_displayed_gallery)) {
			$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
			$this->object->_displayed_gallery = $mapper->find($id, TRUE);
			if (is_null($this->object->_displayed_gallery)) $valid_request = FALSE;
			else $this->object->_displayed_gallery->id = $this->object->_displayed_gallery->id();
		}
		// No displayed gallery was specified
		else {
			$factory = $this->object->get_registry()->get_utility('I_Component_Factory');
			$this->object->_displayed_gallery = $factory->create('displayed_gallery');
		}

		return $valid_request;
	}

	/**
	 * Returns the page title of the Attach to Post interface
	 * @return string
	 */
	function _get_page_title()
	{
		return _('NextGEN Gallery - Attach To Post');
	}


	/**
	 * Returns the main tabs displayed on the Attach to Post interface
	 * @returns array
	 */
	function _get_main_tabs()
	{
		return array(
			'displayed_tab'		=> $this->object->_render_display_tab(),
			'create_tab'		=> $this->object->_render_create_tab(),
			'galleries_tab'		=> $this->object->_render_galleries_tab(),
			'albums_tab'		=> $this->object->_render_albums_tab(),
			'tags_tab'			=> $this->object->_render_tags_tab()
		);
	}

	function _get_main_tab_titles()
	{
		return array(
			_('Display Galleries and Images') => 'displayed_tab',
			_('Create Gallery / Add Images')  => 'create_tab',
			_('Manage Galleries')			  => 'galleries_tab',
			_('Manage Albums')				  => 'albums_tab',
			_('Manage Tags')				  => 'tags_tab',
		);
	}

	/**
	 * Renders a NextGen Gallery page in an iframe, suited for the attach to post
	 * interface
	 * @param string $page
	 * @return string
	 */
	function _render_ngg_page_in_frame($page)
	{
		$frame_url = real_site_url("/wp-admin/admin.php?page={$page}&attach_to_post");
		$frame_url = esc_url($frame_url);

		return "<iframe name='{$page}' frameBorder='0' class='ngg-attach-to-post ngg-iframe-page-{$page}' scrolling='no' src='{$frame_url}'></iframe>";
	}

	/**
	 * Renders the display tab for adjusting how images/galleries will be
	 * displayed
	 * @return type
	 */
	function _render_display_tab()
	{
		return $this->object->render_partial('display_tab', array(
			'messages'	=>	array(),
			'tabs'		=>	$this->object->_get_display_tabs()
		), TRUE);
	}


	/**
	 * Renders the tab used primarily for Gallery and Image creation
	 * @return type
	 */
	function _render_create_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-add-gallery');
	}


	/**
	 * Renders the tab used for Managing Galleries
	 * @return string
	 */
	function _render_galleries_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-manage-gallery');
	}


	/**
	 * Renders the tab used for Managing Albums
	 */
	function _render_albums_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-manage-album');
	}


	/**
	 * Renders the tab used for Managing Albums
	 * @return string
	 */
	function _render_tags_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-tags');
	}
}
