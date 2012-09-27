<?php

class A_NextGen_Basic_Album_Controller extends Mixin
{
    /**
     * Renders the front-end for the NextGen Basic Album display type
     * @param $displayed_gallery
     * @param bool $return
     */
    function index_action($displayed_gallery, $return=FALSE)
    {
        var_dump($displayed_gallery->get_album_entities());
    }
}