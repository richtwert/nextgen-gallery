<?php

class A_Lightbox_Installer extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			get_class().'::Install',
			get_class(),
			'install_lightboxes'
		);

		$this->object->add_post_hook(
			'uninstall',
			get_class().'::Uninstall',
			get_class(),
			'uninstall_lightboxes'
		);
	}

	/**
	 * Installs a lightbox library
	 * @param string $name
	 * @param string $code
	 * @param array $stylesheet_paths
	 * @param array $script_paths
	 * @param array $values
	 */
	function install_lightbox($name, $code, $stylesheet_paths=array(), $script_paths=array(), $values=array())
	{
		// Try to find the existing lightbox. If we can't find it, we'll create
		$router			= $this->get_registry()->get_utility('I_Router');
		$mapper			= $this->get_registry()->get_utility('I_Lightbox_Library_Mapper');
        $lightbox		= $mapper->find_by_name($name);
		if (!$lightbox) $lightbox = new stdClass;

		// Set properties
		$lightbox->name				= $name;
		$lightbox->code				= $code;
		$lightbox->css_stylesheets	= array();
		$lightbox->scripts			= array();
		$lightbox->values			= $values;
		foreach ($stylesheet_paths as $stylesheet) {
			if (preg_match("/http(s)?/", $stylesheet))
				$lightbox->css_stylesheets[] = $stylesheet;
			else
				$lightbox->css_stylesheets[] = $router->get_static_url($stylesheet);
		}
		foreach ($script_paths as $script) {
			if (preg_match("/http(s)?/", $script))
				$lightbox->scripts[]		 = $script;
			else
				$lightbox->scripts[]		 = $router->get_static_url($script);
		}
		$lightbox->css_stylesheets = implode("\n", $lightbox->css_stylesheets);
		$lightbox->scripts		   = implode("\n", $lightbox->scripts);

		// Save the lightbox
		$mapper->save($lightbox);
		unset($mapper);
	}

	/**
	 * Uninstalls an existing lightbox
	 * @param string $name
	 */
	function uninstall_lightbox($name)
	{
		$mapper	= $this->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		if (($lightbox = $mapper->find_by_name($name))) {
			$mapper->destroy($lightbox);
		}
	}

	/**
	 * Installs all of the lightbox provided by this module
	 */
	function install_lightboxes()
	{
		$router = $this->get_registry()->get_utility('I_Router');

		$this->install_lightbox(
			'lightbox',
			"class='ngg_lightbox'",
			array('jquery.lightbox/jquery.lightbox-0.5.css'),
			array(
				'jquery.lightbox/jquery.lightbox-0.5.min.js',
				'jquery.lightbox/nextgen_lightbox_init.js'
			),
			array(
				'nextgen_lightbox_loading_img_url'	=>
				$router->get_static_url('jquery.lightbox/lightbox-ico-loading.gif'),

				'nextgen_lightbox_close_btn_url'	=>
				$router->get_static_url('jquery.lightbox/lightbox-btn-close.gif'),

				'nextgen_lightbox_btn_prev_url'		=>
				$router->get_static_url('jquery.lightbox/lightbox-btn-prev.gif'),

				'nextgen_lightbox_btn_next_url'		=>
				$router->get_static_url('jquery.lightbox/lightbox-btn-next.gif'),

				'nextgen_lightbox_blank_img_url'	=>
				$router->get_static_url('jquery.lightbox/lightbox-blank.gif')
			)
		);

		// Install Fancybox 1.3.4
		$this->install_lightbox(
			'fancybox',
			'class="ngg-fancybox" rel="%GALLERY_NAME%"',
			array('fancybox/jquery.fancybox-1.3.4.css'),
			array(
				'fancybox/jquery.easing-1.3.pack.js',
				'fancybox/jquery.fancybox-1.3.4.pack.js',
				'fancybox/nextgen_fancybox_init.js'
			)
		);

		// Install highslide
		$this->install_lightbox(
			'highslide',
			'class="highslide" onclick="return hs.expand(this, galleryOptions);"',
			array('highslide/highslide.css'),
			array('highslide/highslide-full.packed.js', 'highslide/nextgen_highslide_init.js'),
			array('nextgen_highslide_graphics_dir' => $router->get_static_url('lightbox#highslide/graphics'))
		);

		// Install Shutter
		$this->install_lightbox(
			'shutter',
			'class="shutterset_%GALLERY_NAME%"',
			array('shutter/shutter.css'),
			array('shutter/shutter.js', 'shutter/nextgen_shutter.js'),
			array(
				'msgLoading'	=>	'L O A D I N G',
				'msgClose'		=>	'Click to Close',
			)
		);

		// Install Shutter Reloaded
		$this->install_lightbox(
			'shutter 2.0.1',
			'class="shutterset_%GALLERY_NAME%"',
			array('shutter_reloaded/shutter.css'),
			array('shutter_reloaded/shutter.js', 'shutter_reloaded/nextgen_shutter_reloaded.js')
		);

		// Install Thickbox
		$this->install_lightbox(
			'thickbox',
			"class='thickbox' rel='%GALLERY_NAME%'",
			array(includes_url('/js/thickbox.css')),
			array(includes_url('/js/thickbox.js'), 'lightbox#thickbox/nextgen_thickbox_init.js')
		);
	}

	/**
	 * Uninstalls all lightboxes
	 */
	function uninstall_lightboxes()
	{
		$mapper = $this->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$mapper->delete()->run_query();
	}
}