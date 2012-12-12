<?php

class A_Fancybox_Library_Activation extends Mixin
{
    /**
     * Register our activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'Fancybox Library - Activation',
            get_class($this),
            'install_fancybox_library'
        );
    }

    /**
     * Plugin activation routine - register this with the lightbox library
     */
    function install_fancybox_library()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$fancybox = $mapper->find_by_name('fancybox');
		if (!$fancybox) $fancybox = new stdClass();
		$fancybox->name = 'fancybox';
		$fancybox->code = 'class="ngg-fancybox" rel="%GALLERY_NAME%"';
		$fancybox->css_stylesheets = $this->static_url('/fancybox/jquery.fancybox-'.NEXTGEN_GALLERY_FANCYBOX_VERSION.'.css');
		$fancybox->scripts = implode("\n", array(
			$this->static_url('/fancybox/jquery.easing-1.3.pack.js'),
			$this->static_url('/fancybox/jquery.fancybox-'.NEXTGEN_GALLERY_FANCYBOX_VERSION.'.pack.js' ),
			$this->static_url('/nextgen_fancybox_init.js')
		));
		$mapper->save($fancybox);
    }
}
