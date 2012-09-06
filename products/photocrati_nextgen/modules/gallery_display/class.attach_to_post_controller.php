<?php

/**
 * Provides the ability to create and edit C_Displayed_Galleries
 */
class C_Attach_To_Post_Controller extends C_MVC_Controller
{
	static $_instances = array();
	var $_displayed_gallery;

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

	/**
	 * Returns an instance of the controller
	 * @param mixed $context
	 * @return C_Attach_To_Post_Controller
	 */
	static function get_instance($context=FALSE)
	{
		$klass = get_class();
		if (!isset(self::$_instances[$context])) {
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

/**
 * Provide instance methods for the Attach To Post Controller
 */
class Mixin_Attach_To_Post_Controller extends Mixin
{
	/**
	 * Renders the interface
	 */
	function index()
	{
		// For a valid request, we'll display our tabbed interface
		if ($this->object->_validate_request()) {
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


	/**
	 * Displays a preview image for the displayed gallery
	 */
	function preview()
	{
		$filename = $this->static_file('invalid_image.png');
		$this->set_content_type('jpeg');
		if ($this->object->_validate_request()) {
			$storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');
			$images = $this->object->_displayed_gallery->get_images(1);
			if ($images) $filename = $storage->get_thumb_abspath($images[0]);
		}
		readfile($filename);
		$this->render();
	}


	/**
	 * Enqueues resources needed for the Attach To Post Interface
	 */
	function enqueue_resources()
	{
		define('WP_ADMIN', TRUE);

		if ($this->object->_validate_request()) {

			// Enqueue JQuery UI
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_Script('jquery-ui-accordion');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_style(
				PHOTOCRATI_GALLERY_JQUERY_UI_THEME,
				is_ssl() ?
					 str_replace('http:', 'https:', PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL) :
					 PHOTOCRATI_GALLERY_JQUERY_UI_THEME_URL,
				array(),
				PHOTOCRATI_GALLERY_JQUERY_UI_THEME_VERSION
			);

			// Enqueue chosen, a library to make our drop-downs look pretty
			wp_enqueue_style('chosen', $this->static_url('chosen.css'));
			wp_enqueue_script(
				'chosen', $this->static_url('chosen.js'), array('jquery')
			);

			// Ensure we have the AJAX module ready
			wp_enqueue_script('photocrati_ajax', PHOTOCRATI_GALLERY_AJAX_URL.'/js');

			// Enqueue logic for the Attach to Post interface as a whole
			wp_enqueue_script(
				'ngg_attach_to_post', $this->static_url('attach_to_post.js')
			);
			wp_enqueue_style(
				'ngg_attach_to_post', $this->static_url('attach_to_post.css')
			);

			// Enqueue our Ember.js application for the "Display Tab"
			wp_enqueue_script(
				'handlebars',
				$this->static_url('handlebars-1.0.0.beta.6.js')
			);
			wp_enqueue_script(
				'ember',
				$this->static_url('ember-1.0.pre.js'),
				array('jquery', 'handlebars')
			);
			wp_enqueue_script(
				'ngg_attach_to_post_display_tab_app',
				$this->static_url('display_tab_app.js'),
				array('ember')
			);
			wp_localize_script(
				'ngg_attach_to_post_display_tab_app',
				'ngg_displayed_gallery_preview_url',
				PHOTOCRATI_GALLERY_ATTACH_TO_POST_PREVIEW_URL
			);
			wp_localize_script(
				'ngg_attach_to_post_display_tab_app',
				'existing',
				isset($this->object->_displayed_gallery) ? (array)$this->object->_displayed_gallery->get_entity() : null
			);

			// Tell WordPress to continue print all enqueued resources
			do_action('admin_enqueue_scripts');
			do_action('admin_print_styles');
			do_action('admin_print_scripts');
			do_action('wp_print_scripts');
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
		return "<iframe scrolling='no' src='{$frame_url}'></iframe>";
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