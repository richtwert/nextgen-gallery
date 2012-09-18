<?php

class Mixin_NextGen_Basic_Singlepic_Cache extends Mixin
{
    function cache_file($id, $abspath, $width = '', $height = '', $mode = '')
    {
        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        include_once(nggGallery::graphic_library());

        // cache filename should be unique
        $cachename    = $id . '_' . $mode . '_'. $width . 'x' . $height . '_' . basename($abspath);
        $cachefolder  = WINABSPATH . $settings->gallerypath . 'cache' . DIRECTORY_SEPARATOR;
        $cached_url   = site_url() . DIRECTORY_SEPARATOR . $settings->gallerypath . 'cache' . DIRECTORY_SEPARATOR . $cachename;
        $cached_file  =  $cachefolder . $cachename;

        // check first for the file
        if (file_exists($cached_file))
        {
            return $cached_url;
        }

        // create folder if needed
        if (!file_exists($cachefolder))
        {
            if (!wp_mkdir_p($cachefolder))
            {
                return FALSE;
            }
        }

        $thumb = new ngg_Thumbnail($abspath, TRUE);

        if (!$thumb->error)
        {
            if ('crop' == $mode)
            {
                // calculates the new dimentions for a downsampled image
                list ($ratio_w, $ratio_h) = wp_constrain_dimensions($thumb->currentDimensions['width'],
                                                                    $thumb->currentDimensions['height'],
                                                                    $width,
                                                                    $height);

                // check ratio to decide which side should be resized
                ($ratio_h < $height || $ratio_w == $width) ? $thumb->resize(0, $height) : $thumb->resize($width, 0);

                // get the best start postion to crop from the middle
                $ypos = ($thumb->currentDimensions['height'] - $height) / 2;
                $thumb->crop(0, $ypos, $width, $height);
            }
            else {
                $thumb->resize($width , $height);
            }

            if ('watermark' == $mode)
            {
                if ('image' == $settings->wmType)
                {
                    $thumb->watermarkImgPath = $settings->wmPath;
                    $thumb->watermarkImage($settings->wmPos, $settings->wmXpos, $settings->wmYpos);
                }
                if ('text' == $settings->wmType)
                {
                    $thumb->watermarkText = $settings->wmText;
                    $thumb->watermarkCreateText($settings->wmColor, $settings->wmFont, $settings->wmSize, $settings->wmOpaque);
                    $thumb->watermarkImage($settings->wmPos, $settings->wmXpos, $settings->wmYpos);
                }
            }

            if ('web20' == $mode)
            {
                $thumb->createReflection(40, 40, 50, FALSE, '#a4a4a4');
            }

            // save the new cache picture
            $thumb->save($cached_file, $settings->imgQuality);
        }
        $thumb->destruct();

        // check again for the file
        if (file_exists($cached_file))
        {
            return $cached_url;
        }

        return FALSE;
    }
}
