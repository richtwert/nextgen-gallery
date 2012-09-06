<?php

/**
 * Provides rendering logic for the NextGen Basic ImageBrowser
 */
class A_NextGen_Basic_ImageBrowser_Controller extends Mixin
{
	function index($displayed_gallery, $return=FALSE)
	{
		$picturelist	= array();
		foreach ($displayed_gallery->get_images() as $image) {
			$key = $image->id_field;
			$picturelist[$image->$key] = $image;
		}
		if ($picturelist) {
			$retval = nggCreateImageBrowser(
				$picturelist,
				$displayed_gallery->display_settings['template']
			);
			if ($return) return $retval;
			else echo $retval;
		}
		else
			return $this->object->render_partial("no_images_found", array(), $return);

	}
}