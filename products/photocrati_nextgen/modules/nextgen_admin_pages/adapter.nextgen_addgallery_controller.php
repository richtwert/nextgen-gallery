<?php

class A_NextGen_AddGallery_Controller extends Mixin
{
    function get_page_title()
    {
        return 'Add Gallery / Images';
    }

    function get_required_permission()
    {
        return 'NextGEN Upload images';
    }
}