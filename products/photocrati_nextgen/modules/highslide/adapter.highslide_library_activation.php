<?php

class A_Highslide_Library_Activation extends Mixin
{
    /**
     * Register our activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'Highslide Library - Activation',
            get_class($this),
            'install_highslide_library'
        );
    }

    /**
     * Plugin activation routine - register this with the lightbox library
     */
    function install_highslide_library()
    {
        $mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$highslide = $mapper->find_by_name('highslide');
		if (!$highslide) $highslide = new stdClass();
		$highslide->name =			 'highslide';
		$highslide->code			= 'class="highslide" onclick="return hs.expand(this, galleryOptions);"';
		$highslide->css_stylesheets = $this->static_url('/highslide/highslide.css');
		$highslide->scripts			= implode("\n", array(
			$this->static_url('/highslide/highslide-full.packed.js'),
			$this->static_url('/nextgen_highslide_init.js')
		));
        $highslide->values = array(
            'nextgen_highslide_graphics_dir' => $this->static_url('/highslide/graphics/')
        );

        $mapper->save($highslide);
    }
}
