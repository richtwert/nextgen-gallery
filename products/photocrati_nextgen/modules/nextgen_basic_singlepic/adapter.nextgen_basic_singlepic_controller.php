<?php

class A_NextGen_Basic_Singlepic_Controller extends Mixin
{
    function initialize()
    {
        $this->add_mixin('Mixin_NextGen_Basic_Templates');
        $this->add_mixin('Mixin_NextGen_Basic_Singlepic_Settings');
        $this->add_mixin('Mixin_NextGen_Basic_Singlepic_Cache');
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
        $display_settings['mode'] = (preg_match('/(web20|watermark)/i', $display_settings['mode'])) ? $display_settings['mode'] : '';

        $thumbnail_url = FALSE;
        if ($post->post_status == 'publish')
        {
            /*
            $tmp = $storage->generate_thumbnail(
                $image,
                $display_settings['width'],
                $display_settings['height'],
                NULL, // crop
                NULL, // quality
                $watermark=NULL, // watermark
                $reflection=NULL, // reflection
                $return_thumb=false // return_thumb
            );
            exit;
            */

            $thumbnail_url = $this->object->cache_file(
                $image->pid,
                $storage->get_image_abspath($image),
                $display_settings['width'],
                $display_settings['height'],
                $display_settings['mode']
            );
        }
        if (!$thumbnail_url)
        {
            $thumbnail_url = trailingslashit(home_url()) . 'index.php?callback=image'
                                                         . '&amp;pid='    . $image->pid
                                                         . '&amp;width='  . $display_settings['width']
                                                         . '&amp;height=' . $display_settings['height']
                                                         . '&amp;mode='   . $display_settings['mode'];
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
