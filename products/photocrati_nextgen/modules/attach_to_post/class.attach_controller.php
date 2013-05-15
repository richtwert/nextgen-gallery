<?php

class C_Attach_Controller extends C_NextGen_Admin_Page_Controller
{
	static $_instances = array();
	var	   $_displayed_gallery;

	static function &get_instance($context)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context)
	{
		if (!is_array($context)) $context = array($context);
		array_unshift($context, 'ngg_attach_to_post');
		parent::define($context);
		$this->add_mixin('Mixin_Attach_To_Post');
		$this->add_mixin('Mixin_Attach_To_Post_Display_Tab');
		$this->implement('I_Attach_To_Post_Controller');
	}

	function initialize()
	{
		parent::initialize();
		$this->_load_displayed_gallery();
	}
}

class Mixin_Attach_To_Post extends Mixin
{
	function _load_displayed_gallery()
	{
		$mapper = $this->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
		if (!($this->object->_displayed_gallery = $mapper->find($this->object->param('id'), TRUE))) {
			$this->object->_displayed_gallery = $mapper->create();
		}
	}

	function enqueue_backend_resources()
	{
		$this->call_parent('enqueue_backend_resources');
		// Enqueue frame event publishing
		do_action('admin_enqueue_scripts');
		wp_enqueue_script('frame_event_publisher');

		// Enqueue JQuery UI libraries
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-tooltip');
		wp_enqueue_script('ngg_tabs', $this->get_static_url('attach_to_post#ngg_tabs.js'));

		// Ensure select2
		wp_enqueue_style('select2', $this->get_static_url('attach_to_post#select2.css'));
		wp_enqueue_script('select2', $this->get_static_url('attach_to_post#select2.js'));

		// Ensure that the Photocrati AJAX library is loaded
		wp_enqueue_script('photocrati_ajax');

		// Enqueue logic for the Attach to Post interface as a whole
		wp_enqueue_script(
			'ngg_attach_to_post', $this->get_static_url('attach_to_post#attach_to_post.js')
		);
		wp_enqueue_style(
			'ngg_attach_to_post', $this->get_static_url('attach_to_post#attach_to_post.css')
		);

		// Enqueue our JS templating library, Handlebars
		wp_enqueue_script(
			'handlebars',
			$this->get_static_url('attach_to_post#handlebars-1.0.0.beta.6.js'),
			array(),
			'1.0.0b6'
		);

		// Enqueue backbone.js library, required by the Attach to Post display tab
		wp_enqueue_script('backbone'); // provided by WP

		// Ensure underscore sting, a helper utility
		wp_enqueue_script(
			'underscore.string',
			$this->get_static_url('attach_to_post#underscore.string.js'),
			array('underscore'),
			'2.3.0'
		);

		// Enqueue the backbone app for the display tab
		$settings			= $this->get_registry()->get_utility('I_Settings_Manager');
		$preview_url		= $settings->gallery_preview_url;
		$display_tab_js_url	= $settings->attach_to_post_display_tab_js_url;
		if ($this->object->_displayed_gallery->id()) {
			$display_tab_js_url .= '/id--'.$this->object->_displayed_gallery->id();
		}

		wp_enqueue_script(
			'ngg_display_tab',
			$display_tab_js_url,
			array('backbone', 'underscore.string')
		);
		wp_localize_script(
			'ngg_display_tab',
			'ngg_displayed_gallery_preview_url',
			$settings->gallery_preview_url
		);
	}

	/**
	 * Renders the interface
	 */
	function index_action()
	{
        if ($this->object->_displayed_gallery->is_new()) $this->object->expires("+2 hour");

		// Enqueue resources
		$this->object->render_view('attach_to_post#attach_to_post', array(
			'page_title'	=>	$this->object->_get_page_title(),
			'tabs'			=>	$this->object->_get_main_tabs(),
			'tab_titles'	=>	$this->object->_get_main_tab_titles()
		));
	}


	/**
	 * Displays a preview image for the displayed gallery
	 */
	function preview_action()
	{
		$found_preview_pic = FALSE;

		$dyn_thumbs		= $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');
		$storage		= $this->object->get_registry()->get_utility('I_Gallery_Storage');
		$image_mapper	= $this->object->get_registry()->get_utility('I_Image_Mapper');

		// Get the first entity from the displayed gallery. We will use this
		// for a preview pic
		$entity = array_pop($this->object->_displayed_gallery->get_included_entities(1));
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

		// Render invalid image if no preview pic is found
		if (!$found_preview_pic) {
            $filename = $this->object->get_static_abspath('attach_to_post#invalid_image.png');
			$this->set_content_type('image/png');
			readfile($filename);
			$this->render();
		}
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
			_('Display Galleries') => 'displayed_tab',
			_('Add Gallery / Images')  => 'create_tab',
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
	function _render_ngg_page_in_frame($page, $tab_id = null)
	{
		$frame_url = real_site_url("/wp-admin/admin.php?page={$page}&attach_to_post");
		$frame_url = esc_url($frame_url);

		if ($tab_id) {
			$tab_id = " id='ngg-iframe-{$tab_id}'";
		}

		return "<iframe name='{$page}' frameBorder='0'{$tab_id} class='ngg-attach-to-post ngg-iframe-page-{$page}' scrolling='no' src='{$frame_url}'></iframe>";
	}

	/**
	 * Renders the display tab for adjusting how images/galleries will be
	 * displayed
	 * @return type
	 */
	function _render_display_tab()
	{
		return $this->object->render_partial('attach_to_post#display_tab', array(
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
		return $this->object->_render_ngg_page_in_frame('ngg_addgallery', 'create_tab');
	}


	/**
	 * Renders the tab used for Managing Galleries
	 * @return string
	 */
	function _render_galleries_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-manage-gallery', 'galleries_tab');
	}


	/**
	 * Renders the tab used for Managing Albums
	 */
	function _render_albums_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-manage-album', 'albums_tab');
	}


	/**
	 * Renders the tab used for Managing Albums
	 * @return string
	 */
	function _render_tags_tab()
	{
		return $this->object->_render_ngg_page_in_frame('nggallery-tags', 'tags_tab');
	}
}
