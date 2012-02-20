<?php

class C_NextGen_ImageBrowser_View extends C_Base_Gallery_View_Controller
{
    // Frontend gallery display
    function index()
    {
        // Disable the display of E_NOTICE as NextGen's meta parser
        // generates many of these
        $er = error_reporting(E_ALL ^ E_NOTICE);
        echo nggCreateImageBrowser($this->attached_gallery->get_images(TRUE));
        error_reporting($er);
    }
}