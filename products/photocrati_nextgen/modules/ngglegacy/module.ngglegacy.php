<?php

/***
	{
		Module: photocrati-nextgen-legacy
	}
 ***/

define(
	'NEXTGEN_GALLERY_NGGLEGACY_MOD_DIR',
	path_join(NEXTGEN_GALLERY_MODULE_DIR, basename(dirname(__FILE__)))
);

define(
	'NEXTGEN_GALLERY_NGGLEGACY_MOD_URL',
	path_join(NEXTGEN_GALLERY_MODULE_URL, basename(dirname(__FILE__)))
);

class M_NggLegacy extends C_Base_Module
{
	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen-legacy',
			'NextGEN Legacy',
			'Embeds the original version of NextGEN 1.9.3 by Alex Rabe',
			'0.1',
			'http://www.nextgen-gallery.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	/**
	 * Initializes the module
	 */
	function initialize()
	{
		parent::initialize();
		include_once(path_join(dirname(__FILE__), 'nggallery.php'));
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
		add_action('ngg_update_album_sortorder', array(&$this, 'set_album_previewpic'));
	}

	/**
	 * When an album has been updated, set it's previewpic if not already set
	 * @param int $album_id
	 */
	function set_album_previewpic($album_id)
	{
		$mapper			= $this->get_registry()->get_utility('I_Album_Mapper');
		$album			= $mapper->find($album_id);
		$set_previewpic = FALSE;

		// Set a preview pic if not available
		while (!$album->previewpic) {

			// If the album is missing a preview pic, set one!
			if (($first_entity = array_shift($album->sortorder))) {

				// Is the first entity a gallery or album
				if (substr($first_entity, 0, 1) == 'a') {
					$subalbum = $mapper->find(substr($first_entity, 1));
					if ($subalbum->previewpic) {
						$album->previewpic = $subalbum->previewpic;
						$set_previewpic = TRUE;
					}
				}
				else {
					$gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper');
					$gallery = $gallery_mapper->find($first_entity);
					if ($gallery->previewpic) {
						$album->previewpic = $gallery->previewpic;
						$set_previewpic = TRUE;
					}
				}
			}
			else break;
		}

		// If we changed the previewpic, save the changes
		if ($set_previewpic) $mapper->save($album);
	}
}

new M_NggLegacy();
