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

    function set_attr(&$obj, $key, $val)
    {
        if (!isset($obj->$key))
            $obj->$key = $val;
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
		if (!$lightbox)
            $lightbox = new stdClass;

        $styles  = array();
		foreach ($stylesheet_paths as $stylesheet) {
			if (preg_match("/http(s)?/", $stylesheet))
				$styles[] = $stylesheet;
			else
				$styles[] = $router->get_static_url($stylesheet);
		}

        $scripts = array();
		foreach ($script_paths as $script) {
			if (preg_match("/http(s)?/", $script))
				$scripts[] = $script;
			else
				$scripts[] = $router->get_static_url($script);
		}

        // Set properties
        $lightbox->name	= $name;
        $this->set_attr($lightbox, 'code', $code);
        $this->set_attr($lightbox, 'values', $values);
        $this->set_attr($lightbox, 'css_stylesheets', implode("\n", $styles));
        $this->set_attr($lightbox, 'scripts', implode("\n", $scripts));

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
	function install_lightboxes($product)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$router = $this->get_registry()->get_utility('I_Router');

		$this->install_lightbox(
			'lightbox',
			"class='ngg_lightbox'",
			array('lightbox#jquery.lightbox/jquery.lightbox-0.5.css'),
			array(
				'lightbox#jquery.lightbox/jquery.lightbox-0.5.min.js',
				'lightbox#jquery.lightbox/nextgen_lightbox_init.js'
			),
			array(
				'nextgen_lightbox_loading_img_url'	=>
				$router->get_static_url('lightbox#jquery.lightbox/lightbox-ico-loading.gif'),

				'nextgen_lightbox_close_btn_url'	=>
				$router->get_static_url('lightbox#jquery.lightbox/lightbox-btn-close.gif'),

				'nextgen_lightbox_btn_prev_url'		=>
				$router->get_static_url('lightbox#jquery.lightbox/lightbox-btn-prev.gif'),

				'nextgen_lightbox_btn_next_url'		=>
				$router->get_static_url('lightbox#jquery.lightbox/lightbox-btn-next.gif'),

				'nextgen_lightbox_blank_img_url'	=>
				$router->get_static_url('lightbox#jquery.lightbox/lightbox-blank.gif')
			)
		);

		// Install Fancybox 1.3.4
		$this->install_lightbox(
			'fancybox',
			'class="ngg-fancybox" rel="%GALLERY_NAME%"',
			array('lightbox#fancybox/jquery.fancybox-1.3.4.css'),
			array(
				'lightbox#fancybox/jquery.easing-1.3.pack.js',
				'lightbox#fancybox/jquery.fancybox-1.3.4.pack.js',
				'lightbox#fancybox/nextgen_fancybox_init.js'
			)
		);

		// Install highslide
		$this->install_lightbox(
			'highslide',
			'class="highslide" onclick="return hs.expand(this, galleryOptions);"',
			array('lightbox#highslide/highslide.css'),
			array('lightbox#highslide/highslide-full.packed.js', 'lightbox#highslide/nextgen_highslide_init.js'),
			array('nextgen_highslide_graphics_dir' => $router->get_static_url('lightbox#highslide/graphics'))
		);

		// Install Shutter
		$this->install_lightbox(
			'shutter',
			'class="shutterset_%GALLERY_NAME%"',
			array('lightbox#shutter/shutter.css'),
			array('lightbox#shutter/shutter.js', 'lightbox#shutter/nextgen_shutter.js'),
			array(
				'msgLoading'	=>	'L O A D I N G',
				'msgClose'		=>	'Click to Close',
			)
		);

		// Install Shutter Reloaded
		$this->install_lightbox(
			'shutter2',
			'class="shutterset_%GALLERY_NAME%"',
			array('lightbox#shutter_reloaded/shutter.css'),
			array('lightbox#shutter_reloaded/shutter.js', 'lightbox#shutter_reloaded/nextgen_shutter_reloaded.js')
		);

		// Install Thickbox
		$this->install_lightbox(
			'thickbox',
			"class='thickbox' rel='%GALLERY_NAME%'",
			array(includes_url('/js/thickbox/thickbox.css')),
			array('lightbox#thickbox/nextgen_thickbox_init.js', includes_url('/js/thickbox/thickbox.js'))
		);
	}

	/**
	 * Uninstalls all lightboxes
	 */
	function uninstall_lightboxes($product, $hard = FALSE)
	{
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
        if ($hard)
        {
            $mapper = $this->get_registry()->get_utility('I_Lightbox_Library_Mapper');
            $mapper->delete()->run_query();
        }
	}
}