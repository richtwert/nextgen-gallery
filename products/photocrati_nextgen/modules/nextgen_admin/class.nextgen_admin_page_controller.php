<?php

class C_NextGen_Admin_Page_Controller extends C_MVC_Controller
{
	static $_instances = array();

	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}

	function define($context=FALSE)
	{
		if (is_array($context)) $this->name = $context[0];
		else $this->name = $context;

		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Admin_Page_Instance_Methods');
		$this->implement('I_NextGen_Admin_Page');
	}

	function initialize()
	{
		parent::initialize();
		$this->add_pre_hook(
			'index_action',
			'Enqueue Backend Resources',
			'Hook_NextGen_Admin_Page_Resources',
			'enqueue_backend_resources'
		);
	}
}

class Hook_NextGen_Admin_Page_Resources extends Hook
{
	function enqueue_backend_resources()
	{
		$this->object->enqueue_backend_resources();
	}
}


class Mixin_NextGen_Admin_Page_Instance_Methods extends Mixin
{
	/**
	 * Authorizes the request
	 */
	function is_authorized_request($privilege=NULL)
	{
		if (!$privilege) $privilege = $this->object->get_required_permission();
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$retval = $sec_token = $security->get_request_token($privilege);
		$sec_actor = $security->get_current_actor();

		// Ensure that the user has permission to access this page
		if (!$sec_actor->is_allowed($privilege))
			$retval = FALSE;

		// Ensure that nonce is valid
		if ($this->object->is_post_request() && !$sec_token->check_current_request()) {
			$retval = FALSE;
		}

		return $retval;
	}

	/**
	 * Returns the permission required to access this page
	 * @return string
	 */
	function get_required_permission()
	{
		return $this->object->name;
	}

	/**
	 * Enqueues resources required by a NextGEN Admin page
	 */
	function enqueue_backend_resources()
	{
		$this->object->enqueue_jquery_ui_theme();
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script(
            'nextgen_display_settings_page_placeholder_stub',
            $this->get_static_url('jquery.placeholder.min.js'),
            array('jquery'),
            '2.0.7',
            TRUE
        );
		wp_register_script('iris', $this->get_router()->get_url('/wp-admin/js/iris.min.js', FALSE), array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'));
		wp_register_script('wp-color-picker', $this->get_router()->get_url('/wp-admin/js/color-picker.js', FALSE), array('iris'));
		wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array(
			'clear' => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick' => __( 'Select Color' ),
			'current' => __( 'Current Color' ),
		));
		wp_enqueue_script(
			'nextgen_admin_settings',
			$this->get_static_url('nextgen_admin_settings.js'),
            array('wp-color-picker')
		);
		wp_enqueue_style(
			'nextgen_admin_settings',
			$this->get_static_url('nextgen_admin_settings.css'),
            array('wp-color-picker')
		);
	}

	function enqueue_jquery_ui_theme()
	{
		$settings = $this->get_registry()->get_utility('I_Settings_Manager');
		wp_enqueue_style(
			$settings->jquery_ui_theme,
			is_ssl() ?
				 str_replace('http:', 'https:', $settings->jquery_ui_theme_url) :
				 $settings->jquery_ui_theme_url,
			array(),
			$settings->jquery_ui_theme_version
		);
	}

	/**
	 * Returns the page title
	 * @return string
	 */
	function get_page_title()
	{
		return $this->object->name;
	}

	/**
	 * Returns the page heading
	 * @return string
	 */
	function get_page_heading()
	{
		return $this->object->get_page_title();
	}

	/**
	 * Returns the type of forms to render on this page
	 * @return string
	 */
	function get_form_type()
	{
		return $this->object->context;
	}

	function get_success_message()
	{
		return "Saved successfully";
	}


	/**
	 * Returns an accordion tab, encapsulating the form
	 * @param I_Form $form
	 */
	function to_accordion_tab($form)
	{
		return $this->object->render_partial('nextgen_admin#accordion_tab', array(
			'id'		=>	$form->get_id(),
			'title'		=>	$form->get_title(),
			'content'	=>	$form->render(TRUE)
		), TRUE);
	}

	/**
	 * Returns the
	 * @return type
	 */
	function get_forms()
	{
		$forms = array();
		$form_manager = $this->get_registry()->get_utility('I_Form_Manager');
		foreach ($form_manager->get_forms($this->object->get_form_type()) as $form) {
			$forms[] = $this->get_registry()->get_utility('I_Form', $form);
		}
		return $forms;
	}

	/**
	 * Renders a NextGEN Admin Page using jQuery Accordions
	 */
	function index_action()
	{
		if (($token = $this->object->is_authorized_request())) {
			// Get each form. Validate it and save any changes if this is a post
			// request
			$tabs			= array();
			$errors			= array();
			$success		= $this->object->is_post_request() ?
									$this->object->get_success_message() : '';

			foreach ($this->object->get_forms() as $form) {
				$form->enqueue_static_resources();
				$tabs[] = $this->object->to_accordion_tab($form);
				if ($this->object->is_post_request()) {
					$action = $this->object->param('action');
					if ($form->has_method($action)) {
						if (!$form->$action()) {
							$errors[] = $this->object->show_errors_for($form->get_model());
						}
					}
				}
			}

			// Render the view
			$this->render_partial('nextgen_admin#nextgen_admin_page', array(
				'page_heading'		=>	$this->object->get_page_heading(),
				'tabs'				=>	$tabs,
				'errors'			=>	$errors,
				'success'			=>	$success,
				'form_header'		=>  $token->get_form_html()
			));
		}

		// The user is not authorized to view this page
		else {
			$this->render_view('nextgen_admin#not_authorized', array(
				'name'	=>	$this->object->name,
				'title'	=>	$this->object->get_page_title()
			));
		}
	}
}