<?php

class C_NextGen_Thumbnail_Gallery_Settings extends C_Thumbnail_Settings
{
    function preview()
    {
        $src = $this->static_url('preview.jpg');
        $this->render_partial('preview', array('src' => $src));
    }
}