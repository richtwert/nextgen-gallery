<?php

class A_NextGen_Basic_Slideshow_Controller extends Mixin
{
    public $_settings = array();

	/**
	 * Adds framework support for thumbnails
	 */
	function initialize()
	{
		$this->add_mixin('Mixin_Thumbnail_Display_Type_Controller');
	}

	/**
	 * Displays the ngglegacy thumbnail gallery.
	 * This method deprecated use of the nggShowGallery() function.
	 * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
	 */
	function index_action($displayed_gallery)
	{
		// Get the images to be displayed
        $current_page = get_query_var('nggpage') ? get_query_var('nggpage') : (isset($_GET['nggpage']) ? intval($_GET['nggpage']) : 1);

		$images = $displayed_gallery->get_included_images($displayed_gallery->get_image_count(), 0);

		if (!$images)
        {
            $this->object->render_partial("no_images_found");
            return;
        }

        // Get the gallery storage component
        $storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');

        $params = $displayed_gallery->display_settings;
        $params['storage']				= &$storage;
        $params['images']				= &$images;
        $params['displayed_gallery_id'] = $displayed_gallery->id();
        $params['current_page']			= $current_page;
        $params['effect_code']			= $this->object->get_effect_code($displayed_gallery);

        // If flash slideshow gallery is enabled, generate a playlist using MediaRSS
        if ($displayed_gallery->display_settings['flash_enabled'])
        {
            $transient_handler = $this->object->get_registry()->get_utility('I_Transients');
            $entity = $displayed_gallery->get_entity();
            $transient_handler->set_value('displayed_gallery_' . $entity->ID, $entity);
            $mediarss_link = real_site_url('/mediarss?template=playlist_feed&source=displayed_gallery&transient_id=' . $entity->ID);

            $params['mediarss_link'] = $mediarss_link;

            $this->object->render_partial('nextgen_basic_slideshow_flash', $params);
            return;
        }

        $this->object->render_partial('nextgen_basic_slideshow', $params);
	}

	/**
	 * Enqueues all static resources required by this display type
	 * @param C_Displayed_Gallery $displayed_gallery
	 */
	function enqueue_frontend_resources($displayed_gallery)
	{
		$this->call_parent('enqueue_frontend_resources', $displayed_gallery);
	}


	/**
	 * Provides the url of the JavaScript library required for
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_lib_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow.js';
	}

	/**
	 * Provides the url of the JavaScript resource used to initialize
	 * NextGEN Basic Slideshow to display
	 * @return string
	 */
	function _get_js_init_url()
	{
		return PHOTOCRATI_GALLERY_NEXTGEN_BASIC_SLIDESHOW_JS_URL . '/nextgen_basic_slideshow_init.js';
	}


	function _render_nextgen_basic_slideshow_cycle_interval_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_cycle_interval', array(
					'display_type_name' => $display_type->name,
					'cycle_interval_label' => _('Interval'),
					'cycle_interval' => $display_type->settings['cycle_interval'],
			), True);
	}

	function _render_nextgen_basic_slideshow_cycle_effect_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_cycle_effect', array(
					'display_type_name' => $display_type->name,
					'cycle_effect_label' => _('Effect'),
					'cycle_effect' => $display_type->settings['cycle_effect'],
			), True);
	}

	function _render_nextgen_basic_slideshow_gallery_dimensions_field($display_type)
	{
			return $this->render_partial('nextgen_basic_slideshow_settings_gallery_dimensions', array(
					'display_type_name' => $display_type->name,
					'gallery_dimensions_label' => _('Gallery dimensions'),
					'gallery_width' => $display_type->settings['gallery_width'],
					'gallery_height' => $display_type->settings['gallery_height'],
			), True);
	}

    function _build_settings_array($display_type, $name)
    {
        $label = NULL;
        $text  = NULL;
        $value = isset($display_type->settings[$name]) ? $display_type->settings[$name] : NULL;
        $type  = 'text';
        $color = FALSE;
        $attr  = NULL;

        if (is_bool($value))
        {
            $type = 'radio';
        }

        switch ($name)
        {
            case 'flash_enabled':
                $type = 'radio';
                $label = __('Enable flash slideshow', 'nggallery');
                $text = __('Integrate the flash based slideshow for all flash supported devices', 'nggallery');
                break;
            case 'flash_path':
                // XXX button search
                $label = __('Path to the imagerotator (URL)', 'nggallery');
                $attr = array('placeholder' => 'http://...', 'class' => 'url_field');
                break;
            case 'flash_shuffle':
                $type = 'radio';
                $label = __('Shuffle?', 'nggallery');
                break;
            case 'flash_next_on_click':
                $type = 'radio';
                $label = __('Show next image on click', 'nggallery');
                break;
            case 'flash_navigation_bar':
                $type = 'radio';
                $label = __('Show navigation bar', 'nggallery');
                break;
            case 'flash_loading_icon':
                $type = 'radio';
                $label = __('Show loading icon', 'nggallery');
                break;
            case 'flash_watermark_logo':
                $type = 'radio';
                $label = __('Use watermark logo', 'nggallery');
                $text = __('You can change the logo at the watermark settings', 'nggallery');
                break;
            case 'flash_stretch_image':
                $label = __('Stretch image', 'nggallery');
                break;
            case 'flash_transition_effect':
                $label = __('Transition / fade effect', 'nggallery');
                break;
            case 'flash_slow_zoom':
                $type = 'radio';
                $label = __('Use slow zooming effect', 'nggallery');
                break;
            case 'flash_background_color':
                $label = __('Background', 'nggallery');
                $color = TRUE;
                break;
            case 'flash_text_color':
                $label = __('Texts / buttons', 'nggallery');
                $color = TRUE;
                break;
            case 'flash_rollover_color':
                $label = __('Rollover / active', 'nggallery');
                $color = TRUE;
                break;
            case 'flash_screen_color':
                $label = __('Screen', 'nggallery');
                $color = TRUE;
                break;
            case 'flash_background_music':
                $label = __('Background music (URL)', 'nggallery');
                $attr = array('placeholder' => 'http://...');
                break;
            case 'flash_xhtml_validation':
                $type = 'radio';
                $label = __('Try XHTML validation (with CDATA)', 'nggallery');
                $text = __('Important: Could cause problems with some browsers.', 'nggallery');
                break;
        }

        // necessary for javascript effect
        if ($color)
        {
            $value = strpos($value, '#') === 0 ? $value : '#' . $value;
        }

        return array(
            'display_type_name' => $display_type->name,
            'hidden' => (TRUE == $display_type->settings['flash_enabled']) ? FALSE : TRUE,
            'label'  => _($label),
            'name'   => $name,
            'text'   => _($text),
            'type'   => $type,
            'value'  => $value,
            'color'  => $color,
            'attr'   => $attr
        );
    }

	function _render_nextgen_basic_slideshow_field_quick_render($display_type, $function_name)
	{
        $match = NULL;

        if (preg_match('/_render_nextgen_basic_slideshow_(\w+)_field/', $function_name, $match))
        {
            $name = $match[1];
        }
        else {
            return NULL;
        }

        $special_fields = array(
            'flash_enabled',
            'flash_stretch_image',
            'flash_transition_effect',
        );
        $color_fields = array(
            'flash_background_color',
            'flash_text_color',
            'flash_rollover_color',
            'flash_screen_color'
        );

        if (in_array($name, $special_fields))
        {
            $template = $name;
        }
        elseif (in_array($name, $color_fields))
        {
            $template = 'colors';
        }
        else {
            $template = 'default';
        }

        $settings = $this->object->_build_settings_array($display_type, $name);

        if ('default' == $template && 'radio' == $settings['type'])
        {
            $template = 'radio';
        }

        return $this->render_partial(
            'nextgen_basic_slideshow_settings_' . $template,
            $settings,
            True
        );
    }

	function _render_nextgen_basic_slideshow_flash_enabled_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_path_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_shuffle_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_next_on_click_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_navigation_bar_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_loading_icon_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_watermark_logo_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_stretch_image_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_transition_effect_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_slow_zoom_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_background_music_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

	function _render_nextgen_basic_slideshow_flash_xhtml_validation_field($display_type)
	{
		return $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, __FUNCTION__);
	}

    function _render_nextgen_basic_slideshow_flash_colors_wrapper_field($display_type)
    {
        $output = array();
        $fields = array(
            '_render_nextgen_basic_slideshow_flash_background_color_field',
            '_render_nextgen_basic_slideshow_flash_text_color_field',
            '_render_nextgen_basic_slideshow_flash_rollover_color_field',
            '_render_nextgen_basic_slideshow_flash_screen_color_field'
        );

        foreach ($fields as $field) {
            $output[] = $this->_render_nextgen_basic_slideshow_field_quick_render($display_type, $field);
        }

        return $this->render_partial(
            'nextgen_basic_slideshow_settings_colors_wrapper',
            array(
                'output' => $output,
                'hidden' => (TRUE == $display_type->settings['flash_enabled']) ? FALSE : TRUE,
            ),
            True
        );
    }

	/**
	 * Returns a list of fields to render on the settings page
	 */
	function _get_field_names()
	{
		return array(
			//'thumbnail_dimensions',
			'nextgen_basic_slideshow_gallery_dimensions',
			'nextgen_basic_slideshow_cycle_interval',
			'nextgen_basic_slideshow_cycle_effect',

			'nextgen_basic_slideshow_flash_enabled',
			'nextgen_basic_slideshow_flash_path',
            'nextgen_basic_slideshow_flash_background_music',
            'nextgen_basic_slideshow_flash_stretch_image',
            'nextgen_basic_slideshow_flash_transition_effect',
			'nextgen_basic_slideshow_flash_shuffle',
			'nextgen_basic_slideshow_flash_next_on_click',
			'nextgen_basic_slideshow_flash_navigation_bar',
			'nextgen_basic_slideshow_flash_loading_icon',
			'nextgen_basic_slideshow_flash_watermark_logo',
			'nextgen_basic_slideshow_flash_slow_zoom',
            'nextgen_basic_slideshow_flash_xhtml_validation',

            'nextgen_basic_slideshow_flash_colors_wrapper'
		);
	}
}
