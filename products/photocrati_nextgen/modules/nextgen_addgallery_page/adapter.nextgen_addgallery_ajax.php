<?php

class A_NextGen_AddGallery_Ajax extends Mixin
{
    function upload_image_action()
    {
        $retval = array();

        $gallery_id     = intval($this->param('gallery_id'));
        $gallery_name   = $this->param('gallery_name');
        $error          = FALSE;

        // We need to create a gallery
        if ($gallery_id == 0) {
            if (strlen($gallery_name) > 0) {
                $gallery_mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
                $gallery = $gallery_mapper->create(array(
                    'title' =>  $gallery_name
                ));
                if (!$gallery->save()) {
                    $retval['error'] = $gallery->get_errors();
                    $error = TRUE;
                }
                else {
                    $gallery_id = $gallery->id();
                }
            }
            else {
                $error = TRUE;
                $retval['error'] = "No gallery name specified";
            }
        }

        // Upload the image to the gallery
        if (!$error) {
            $retval['gallery_id'] = $gallery_id;
            $storage = $this->object->get_registry()->get_utility('I_Gallery_Storage');

            try{
                if (($image = $storage->upload_image($gallery_id))) {
                    $retval['image_id'] = $image->id();
                }
                else {
                    $retval['error'] = 'Image generation failed';
                    $error = TRUE;
                }
            }
            catch (E_InsufficientWriteAccessException $ex) {
                $retval['error'] = $ex->getMessage();
                $error = TRUE;
            }
            catch (Exception $ex) {
                $retval['error']            = "An unexpected error occured.";
                $retval['error_details']    = $ex->getMessage();
                $error = TRUE;
            }
        }

        if ($error) header('HTTP/1.1 400 Bad Request');

        return $retval;
    }
}