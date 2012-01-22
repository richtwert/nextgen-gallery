<?php

class C_AutoUpdate_Admin_Controller extends C_MVC_Controller
{
    function define()
    {
        parent::define();
        $this->implement('I_Admin_Controller');
    }
    
    
    function admin_page()
    {
        $this->render_partial('admin_page', array());
    }
}
