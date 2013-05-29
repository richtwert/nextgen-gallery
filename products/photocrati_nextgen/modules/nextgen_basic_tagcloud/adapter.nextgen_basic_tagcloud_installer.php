<?php

class A_NextGen_Basic_Tagcloud_Installer extends Mixin
{
    /**
     * Adds the activation routine
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'install',
            'NextGEN Basic Tagcloud - Activation',
            get_class($this),
            'install_nextgen_basic_tagcloud'
        );
    }

    /**
     * Installs the display type for NextGEN Basic Tagcloud
     */
    function install_nextgen_basic_tagcloud($product)
    {
        if ($product != NEXTGEN_GALLERY_PLUGIN_BASENAME) { return; }
		$this->object->install_display_type(
			NEXTGEN_BASIC_TAG_CLOUD_MODULE_NAME, array(
				'title'					=>	'NextGEN Basic TagCloud',
				'entity_types'			=>	array('image'),
				'preview_image_relpath'	=>	'nextgen_basic_tagcloud#preview.gif',
				'default_source'		=>	'tags',
				'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 100
			)

		);
    }
}
