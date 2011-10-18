<?php


class C_NextGen_Thumbnail_Gallery_Config extends C_Thumbnail_Config
{   
    function define()
    {
        parent::define();
        $this->implements('I_Gallery_Type');
    }
}