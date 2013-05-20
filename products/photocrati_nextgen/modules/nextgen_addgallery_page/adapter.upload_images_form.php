<?php

class A_Upload_Images_Form extends Mixin
{
    function get_title()
    {
        return "Upload Images";
    }


    function enqueue_static_resources()
    {
        wp_enqueue_style('plupload.queue');
        wp_enqueue_script('browserplus');
        wp_enqueue_script('plupload.queue');

    }

    function render()
    {
        return $this->object->render_partial('nextgen_addgallery_page#upload_images', array(
            'plupload_options' => json_encode($this->object->get_plupload_options()),
            'galleries'        => $this->object->get_galleries()
        ), TRUE);
    }

    function get_plupload_options()
    {
        $retval = array();

        // Generate default Plupload options
        $retval['runtimes']             = 'gears,browserplus,html5,flash,silverlight,html4';
        $retval['max_file_size']        = strval(round( (int) wp_max_upload_size() / 1024 )).'kb';
        $retval['filters']              = $this->object->get_plupload_filters();
        $retval['flash_swf_url']        = includes_url('js/plupload/plupload.flash.swf');
        $retval['silverlight_xap_url']  = includes_url('js/plupload/plupload.silverlight.xap');
        $retval['multipart']            = TRUE;
        $retval['debug']                = TRUE;
        $retval['multipart_params']     = array(
            'gallery_id'    =>  0,
            'action'        =>  'upload_image',
            'gallery_name'  =>  ''
        );

        return $retval;
    }

    function get_plupload_filters()
    {
        $retval = array();

        $imgs               = new stdClass;
        $imgs->title        = "Image files";
        $imgs->extensions   = "jpg,gif,png,JPG,JPEG,GIF,PNG";
        $retval[]           = $imgs;

        $zips               = new stdClass;
        $zips->title        = "Zip files";
        $zips->extensions   = "zip,ZIP";
        $retval[]           = $zips;

        return $retval;
    }

    function get_galleries()
    {
        $gallery_mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
        return $gallery_mapper->find_all();
    }
}