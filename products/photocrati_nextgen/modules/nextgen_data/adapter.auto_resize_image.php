<?php

class A_Auto_Resize_Image extends Hook
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save',
            'auto_resize_image',
            get_class($this),
            'auto_resize_image'
        );
    }

    function auto_resize_image()
    {
		// If the model isn't valid, then don't even attempt resizing images
		if (!$this->object->is_valid()) return;

		// Get the settings class
        $options = $this->get_registry()->get_utility('I_NextGen_Settings');

        // Resize
        if ($options->imgAutoResize) {
            $sizetmp = @getimagesize ( $this->object->get_filename());
            $widthtmp  = $options->imgWidth;
            $heighttmp = $options->imgHeight;
            if (($sizetmp[0] > $widthtmp && $widthtmp) || ($sizetmp[1] > $heighttmp && $heighttmp)) {
                require_once(path_join(NGGALLERY_ABSPATH, 'admin/functions.php'));
                nggAdmin::resize_image($this->object->id());
            }
        }

        // Update the size in the meta data
        $size = @getimagesize ( $this->object->get_filename());
        $meta = array('width' => $size[0] ,'height' => $size[1]);
        $this->object->merge_meta($meta);
        $this->call_anchor();
    }
}