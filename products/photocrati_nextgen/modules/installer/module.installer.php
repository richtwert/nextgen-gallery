<?php

class M_Installer extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-installer',
			'Installer',
			'Provides an installer for modules to use',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}
}

new M_Installer;