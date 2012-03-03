<?php

class C_NextGen_ImageBrowser_View extends C_Base_Gallery_View_Controller
{
    function enqueue_stylesheets()
    {
        $this->resource_loader->enqueue_stylesheet('nextgen_imagebrowser');
    }
    
    
    // Frontend gallery display
    function index()
    {
        // Disable the display of E_NOTICE as NextGen's meta parser
        // generates many of these
        echo nggShowImageBrowser($this->config, $this->config->display_template);
    }
}