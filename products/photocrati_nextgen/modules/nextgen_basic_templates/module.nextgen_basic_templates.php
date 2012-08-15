<?php

/***
{
    Module: photocrati-nextgen_basic_templates,
    Depends: { photocrati-gallery_display, photocrati-thumbnails }
}
 ***/

class M_NextGen_Basic_Templates extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_basic_templates',
			'NextGen Basic Templates',
			'Provides a NextGen-Legacy compatible thumbnail gallery for NextGEN Gallery',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}
}

new M_NextGen_Basic_Templates();
