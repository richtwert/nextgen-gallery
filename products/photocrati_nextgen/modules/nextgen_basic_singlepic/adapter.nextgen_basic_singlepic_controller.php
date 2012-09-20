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
        global $post;

        $storage  = $this->object->get_registry()->get_utility('I_Gallery_Storage');
        $imap     = $this->object->get_registry()->get_utility('I_Gallery_Image_Mapper');

        $display_settings = $displayed_gallery->display_settings;

        $image = $imap->find($displayed_gallery->image_id);

        if (!$image)
        {
            return $this->object->render_partial("no_images_found", array(), $return);
        }

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

        // validate and/or clean our passed settings
        $display_settings['link'] = (!empty($display_settings['link'])) ? $display_settings['link'] : $storage->get_image_url($image);

        // mode is a legacy parameter
        if (!is_array($display_settings['mode'])) $display_settings['mode'] = explode(',', $display_settings['mode']);
        if (in_array('web20', $display_settings['mode']))
            $display_settings['display_reflection'] = TRUE;
        if (in_array('watermark', $display_settings['mode']))
            $display_settings['display_watermark'] = TRUE;

        // legacy assumed no width/height meant full size unlike generate_thumbnail: force a full resolution
        if (empty($display_settings['width']))  $display_settings['width']  = $image->meta_data['width'];
        if (empty($display_settings['height'])) $display_settings['height'] = $image->meta_data['height'];

        $thumbnail_url = FALSE;
        if ($post->post_status == 'publish')
        {
            $thumb = $storage->generate_thumbnail(
                $image,
                $display_settings['width'],
                $display_settings['height'],
                (bool)$display_settings['crop'],
                (int)$display_settings['quality'],
                (!empty($display_settings['display_watermark']) ? 'text' : NULL),
                (bool)$display_settings['display_reflection'],
                TRUE, // return_thumb
                TRUE  // combine_filename
            );
            if (empty($thumb->error))
            {
                $thumbnail_url = str_replace(ABSPATH, site_url() . '/', $thumb->fileName);
            }
        }

        // we couldn't resize it this time, so give them a URL that will do it on the fly
        if (!$thumbnail_url)
        {
            $thumbnail_url = trailingslashit(home_url()) . 'nextgen_image'
                                                         . '/' . $image->pid
                                                         . '/' . $display_settings['width']
                                                         . 'x' . $display_settings['height']
                                                         . '/' . $display_settings['quality']
                                                         . '/' . $display_settings['crop']
                                                         . '/' . $display_settings['display_watermark']
                                                         . '/' . $display_settings['display_reflection']
                                                         . '/';
        }

        if (!empty($display_settings['template']))
        {
            $params = $this->object->prepare_legacy_parameters(array($image), $displayed_gallery, array('single_image' => TRUE));

            // the wrapper is a lazy-loader that calculates variables when requested. We here override those to always
            // return the same precalculated settings provided
            $params['image']->container[0]->_cache_overrides['caption']      = $displayed_gallery->inner_content;
            $params['image']->container[0]->_cache_overrides['classname']    = 'ngg-singlepic ' . $display_settings['float'];
            $params['image']->container[0]->_cache_overrides['imageURL']     = $display_settings['link'];
            $params['image']->container[0]->_cache_overrides['thumbnailURL'] = $thumbnail_url;

            return $this->object->legacy_render($display_settings['template'], $params, $return);
        }
        else {
            $params = $display_settings;
            $params['storage']       = &$storage;
            $params['image']         = &$image;
            $params['effect_code']   = $this->object->get_effect_code($displayed_gallery);
            $params['inner_content'] = $displayed_gallery->inner_content;
            $params['settings']      = $display_settings;
            $params['thumbnail_url'] = $thumbnail_url;
            return $this->object->render_partial('nextgen_basic_singlepic', $params, $return);
        }
    }

}
