<?php

class A_NextGen_Basic_Singlepic_Controller extends Mixin
{
    function initialize()
    {
        $this->add_mixin('Mixin_NextGen_Basic_Templates');
        $this->add_mixin('Mixin_NextGen_Basic_Singlepic_Settings');
    }

    /**
     * Displays the 'singlepic' display type
     *
     * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
     */
    function index_action($displayed_gallery, $return = FALSE)
    {
        $storage  = $this->object->get_registry()->get_utility('I_Gallery_Storage');
        $imap     = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');

        $display_settings = $displayed_gallery->display_settings;

        $image = $imap->find($display_settings['image_id']);

        if (!$image)
        {
            return $this->object->render_partial("no_images_found", array(), $return);
        }

        // validate and/or clean our passed settings
        switch ($display_settings['float']) {
            case 'left':
                $display_settings['float'] = 'ngg-left';
                break;
            case 'right':
                $display_settings['float'] = 'ngg-right';
                break;
            case 'center':
                $display_settings['float'] = 'ngg-center';
                break;
            default:
                $display_settings['float'] = '';
                break;
        }
        $display_settings['link'] = (!empty($display_settings['link'])) ? $display_settings['link'] : $storage->get_image_url($image);

        /* We don't support the "mode" argument just yet
         *
         * $display_settings['mode'] = (preg_match('/(web20|watermark)/i', $display_settings['mode'])) ? $display_settings['mode'] : '';
         * $picture->thumbnailURL = false;
         * if ( $post->post_status == 'publish' )
         *    $picture->thumbnailURL = $picture->cached_singlepic_file($width, $height, $mode );
         * if (!$image->thumbnailURL)
         *     $image->thumbnailURL = trailingslashit(home_url()) . 'index.php?callback=image&amp;pid=' . $image->pid
         *                                                          . '&amp;width=' . $width
         *                                                          . '&amp;height=' . $height
         *                                                          . '&amp;mode=' . $mode;
         */

        if (!empty($display_settings['template']))
        {
            print "add support";
            exit;
            // $params = $this->object->prepare_legacy_parameters($images, $displayed_gallery, NULL, NULL, NULL);
            // return $this->object->legacy_render($display_settings['template'], $params, $return);
        }
        else {
            $params = $display_settings;
            $params['storage']       = &$storage;
            $params['image']         = &$image;
            $params['effect_code']   = $this->object->get_effect_code($displayed_gallery);
            $params['inner_content'] = $displayed_gallery->inner_content;
            $params['settings']      = $display_settings;
            return $this->object->render_partial('nextgen_basic_singlepic', $params, $return);
        }
    }

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array(
            'nextgen_basic_singlepic_dimensions',
            'nextgen_basic_singlepic_link',
            'nextgen_basic_singlepic_mode',
            'nextgen_basic_singlepic_float',
            'nextgen_basic_templates_template'
        );
    }
}
