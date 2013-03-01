<?php

class C_Form extends C_MVC_Controller
{
	static $_instances = array();

	/**
	 * Gets an instance of a form
	 * @param string $context
	 * @return C_Form
	 */
	static function &get_instance($context)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	/**
	 * Defines the form
	 * @param string $context
	 */
	function define($context)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Form_Instance_Methods');
		$this->add_mixin('Mixin_Form_Field_Generators');
		$this->implement('I_Form');
	}
}

class Mixin_Form_Instance_Methods extends Mixin
{
	/**
	 * Enqueues any static resources required by the form
	 */
	function enqueue_static_resources()
	{
	}

	/**
	 * Gets a list of fields to render
	 * @return array
	 */
	function _get_field_names()
	{
		return array();
	}

	/**
	 * Returns datmapper model
	 * @throws ErrorException
	 * @returns C_DataMapper_Model
	 */
	function get_model()
	{
		throw new ErrorException("C_Form::get_model() not implemented with {$this->object->context} context.");
	}

	function get_id()
	{
		return $this->object->context;
	}

	function get_title()
	{
		return $this->object->context;
	}

	/**
	 * Saves the form/model
	 * @param array $attributes
	 * @return type
	 */
	function save($attributes=array())
	{
		return $this->object->get_model()->save($attributes);
	}

	/**
	 * Returns the rendered form
	 */
	function render()
	{
		$fields = array();
		foreach ($this->object->_get_field_names() as $field) {
			$method = "_render_{$field}_field";
			if ($this->object->has_method($method)) {
				$fields[] = $this->object->$method($this->object->get_model());
			}
		}

		return $this->render_partial('form', array(
			'fields'	=>	$fields
		), TRUE);
	}
}

/**
 * Provides some default field generators for forms to use
 */
class Mixin_Form_Field_Generators extends Mixin
{
	function _render_select_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $options)
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_select',
            array(
                'display_type_name' => $display_type->name,
                'name'    => $name,
                'label'   => _($label),
                'options' => $options,
                'value'   => $value,
                'text'    => $text,
                'hidden'  => $hidden
            ),
            True
        );
    }

    function _render_radio_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_radio',
            array(
                'display_type_name' => $display_type->name,
                'name'   => $name,
                'label'  => _($label),
                'value'  => $value,
                'text'   => $text,
                'hidden' => $hidden
            ),
            True
        );
    }

    function _render_number_field($display_type,
                                  $name,
                                  $label,
                                  $value,
                                  $text = '',
                                  $hidden = FALSE,
                                  $placeholder = '',
                                  $min = NULL,
                                  $max = NULL)
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_number',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden,
                'placeholder' => $placeholder,
                'min' => $min,
                'max' => $max
            ),
            True
        );
    }

    function _render_text_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $placeholder = '')
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_text',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden,
                'placeholder' => $placeholder
            ),
            True
        );
    }

    function _render_color_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_color',
            array(
                'display_type_name' => $display_type->name,
                'name'  => $name,
                'label' => _($label),
                'value' => $value,
                'text' => $text,
                'hidden' => $hidden
            ),
            True
        );
    }

    /**
     * Renders the thumbnail override settings field(s)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_thumbnail_override_settings_field($display_type)
    {
        $override_field = $this->_render_radio_field(
            $display_type,
            'override_thumbnail_settings',
            'Override thumbnail settings',
            $display_type->settings['override_thumbnail_settings'],
			'Overriding the thumbnail settings will create an additional set of thumbnails'
        );

        $dimensions_field = $this->render_partial(
            'field_generator/thumbnail_settings',
            array(
                'display_type_name' => $display_type->name,
                'name' => 'thumbnail_dimensions',
                'label'=> _('Thumbnail dimensions'),
                'thumbnail_width' => $display_type->settings['thumbnail_width'],
                'thumbnail_height'=> $display_type->settings['thumbnail_height'],
                'hidden' => empty($display_type->settings['override_thumbnail_settings']) ? 'hidden' : '',
                'text' => ''
            ),
            TRUE
        );

        $qualities = array();
        for ($i = 100; $i > 50; $i--) { $qualities[$i] = "{$i}%"; }
        $quality_field = $this->_render_select_field(
            $display_type,
            'thumbnail_quality',
            'Thumbnail quality',
            $display_type->settings['thumbnail_quality'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE,
            $qualities
        );

        $crop_field = $this->_render_radio_field(
            $display_type,
            'thumbnail_crop',
            'Thumbnail crop',
            $display_type->settings['thumbnail_crop'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE
        );

        $watermark_field = $this->_render_radio_field(
            $display_type,
            'thumbnail_watermark',
            'Thumbnail watermark',
            $display_type->settings['thumbnail_watermark'],
            '',
            empty($display_type->settings['override_thumbnail_settings']) ? TRUE : FALSE
        );

        $everything = $override_field . $dimensions_field . $quality_field . $crop_field . $watermark_field;

        return $everything;
    }

    /**
     * Renders the thumbnail override settings field(s)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_image_override_settings_field($display_type)
    {
        $override_field = $this->_render_radio_field(
            $display_type,
            'override_image_settings',
            'Override image settings',
            $display_type->settings['override_image_settings'],
			'Overriding the image settings will create an additional set of images'
        );

        $qualities = array();
        for ($i = 100; $i > 50; $i--) { $qualities[$i] = "{$i}%"; }
        $quality_field = $this->_render_select_field(
            $display_type,
            'image_quality',
            'Image quality',
            $display_type->settings['image_quality'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE,
            $qualities
        );

        $crop_field = $this->_render_radio_field(
            $display_type,
            'image_crop',
            'Image crop',
            $display_type->settings['image_crop'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE
        );

        $watermark_field = $this->_render_radio_field(
            $display_type,
            'image_watermark',
            'Image watermark',
            $display_type->settings['image_watermark'],
            '',
            empty($display_type->settings['override_image_settings']) ? TRUE : FALSE
        );

        $everything = $override_field . $quality_field . $crop_field . $watermark_field;

        return $everything;
    }

    /**
     * Renders a pair of fields for width and width-units (px, em, etc)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    function _render_width_and_unit_field($display_type)
    {
        return $this->object->render_partial(
            'field_generator/nextgen_settings_field_width_and_unit',
            array(
                'display_type_name' => $display_type->name,
                'name' => 'width',
                'label' => 'Gallery width',
                'value' => $display_type->settings['width'],
                'text' => 'An empty or "0" setting will make the gallery full width',
                'placeholder' => '(optional)',
                'unit_name' => 'width_unit',
                'unit_value' => $display_type->settings['width_unit'],
                'options' => array('px' => 'Pixels', '%' => 'Percent')
            ),
            TRUE
        );
    }

    function _get_aspect_ratio_options()
    {
        return array(
            '0'     => 'Device/Browser default',
            '1.5'   => '3:2 [1.5]',
            '1.333' => '4:3 [1.333]',
            '1.777' => '16:9 [1.777]',
            '1.6'   => '16:10 [1.6]',
            '1.85'  => '1.85:1 [1.85]',
            '2.39'  => '2.39:1 [2.39]',
            '1.81'  => '1.81:1 [1.81]',
            '1'     => '1:1 (Square) [1]'
        );
    }

	/**
	 * Renders the "Show return Link" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_return_link_text_field($display_type)
	{
		return $this->render_partial(
			'nextgen_gallery_display#field_generator/return_link_text',
			array(
				'display_type_name'			=>	$display_type->name,
				'return_link_text_label'	=>	_('Return link text'),
				'tooltip'					=>	_('The text used for the return
												link when using an alternative view, such as a Slideshow'),
				'return_link_text'			=>	$display_type->settings['return_link_text'],
                'hidden'                    => empty($display_type->settings['show_return_link']) ? TRUE : FALSE
			),
			TRUE
		);
	}


	/**
	 * Renders the "Return link text" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_show_return_link_field($display_type)
	{
		return $this->render_partial(
			'nextgen_gallery_display#field_generator/show_return_link',
			array(
				'display_type_name'			=>	$display_type->name,
				'show_return_link_label'	=>	_('Show return link'),
				'tooltip'					=>	_('When viewing as a Slideshow,
												   do you want a return link to
												   display Thumbnails?'),
				'show_return_link'			=>	$display_type->settings['show_return_link']
			),
			TRUE
		);
	}

	/**
	 * Renders the "Show alternative view link" settings field
	 * @param C_Display_Type $display_type
	 * @return string
	 */
	function _render_alternative_view_field($display_type, $template_overrides=array())
	{
		// Params for template
		$template_params = array(
			'display_type_name'			=>	$display_type->name,
			'show_alt_view_link_label'	=>	_('Alternative view link'),
			'tooltip'					=>	_('Show a link that allows end-users to change how a gallery is displayed'),
			'alternative_view'			=>	$display_type->settings['alternative_view'],
			'altviews'					=>	$this->object->_get_alternative_views($display_type),
            'hidden'                    => empty($display_type->settings['show_alternative_view_link']) ? TRUE : FALSE
		);

		// Apply overrides
		$template_params = $this->array_merge_assoc(
			$template_params, $template_overrides,TRUE
		);

		// Render the template
		return $this->render_partial(
			'nextgen_gallery_display#field_generator/alternative_view',
			$template_params,
			TRUE
		);
	}

	/**
	 * Renders the "Alternative view link text" settings field
	 * @param type $display_type
	 * @param type $template_overrides
	 * @return type
	 */
	function _render_alternative_view_link_text_field($display_type, $template_overrides=array()){
		// Params for template
		$template_params = array(
			'display_type_name'				=>	$display_type->name,
			'alt_view_link_text_label'		=>	_('Alternative view link text'),
			'tooltip'						=>	_('The text of the link used to display the alternative view'),
			'alternative_view_link_text'	=>	$display_type->settings['alternative_view_link_text'],
            'hidden'                        => empty($display_type->settings['show_alternative_view_link']) ? TRUE : FALSE
		);

		// Apply overrides
		$template_params = $this->array_merge_assoc(
			$template_params, $template_overrides,TRUE
		);

		// Render the template
		return $this->render_partial(
			'nextgen_gallery_display#field_generator/alt_view_link_text',
			$template_params,
			TRUE
		);
	}


	function _render_show_alternative_view_link_field($display_type, $template_overrides=array())
	{
		// Params for template
		$template_params = array(
			'display_type_name'			=>	$display_type->name,
			'show_alt_view_link_label'	=>	_('Show alternative view link'),
			'tooltip'					=>	_('When enabled, show a link for the user to activate an alternative view'),
			'show_alternative_view_link'=>	$display_type->settings['show_alternative_view_link']
		);

		// Apply overrides
		$template_params = $this->array_merge_assoc(
			$template_params, $template_overrides,TRUE
		);

		// Render the template
		return $this->render_partial(
			'nextgen_gallery_display#field_generator/show_altview_link',
			$template_params,
			TRUE
		);
	}
}