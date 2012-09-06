<?php

class A_Thickbox_Library_Activation extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'install',
			'Thickbox Library - Activation',
			get_class($this),
			'install_thickbox_library'
		);
	}

	function install_thickbox_library()
	{
		$mapper = $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
		$thickbox = $mapper->find_by_name('thickbox');
		if (!$thickbox) $thickbox = new stdClass();
		$thickbox->name = 'thickbox';
		$thickbox->code = "class='thickbox' rel='%GALLERY_NAME%'";
		$thickbox->css_stylesheets = $this->_get_url_for_registered_resource('thickbox', 'style');
		$thickbox->scripts = implode("\n", array(
			$this->_get_url_for_registered_resource('thickbox', 'script'),
			$this->static_url('/nextgen_thickbox_init.js')
		));
		$mapper->save($thickbox);
	}

	function _get_url_for_registered_resource($handle, $type)
	{
		$retval = '';

		if ($type == 'script') {
			global $wp_scripts;
			$retval = $wp_scripts->registered[$handle]->src;
		}
		else {
			global $wp_styles;
			$retval = $wp_styles->registered[$handle]->src;
		}

		return real_site_url($retval);
	}
}