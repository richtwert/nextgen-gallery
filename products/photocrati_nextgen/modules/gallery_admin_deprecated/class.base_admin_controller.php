<?php

class C_Base_Admin_Controller extends C_MVC_Controller
{
    function define()
    {
        parent::define();
        $this->implement('I_Admin_Controller');
    }
    
    
    function _render_accordion($tabs=array(), $return=FALSE)
    {
        return $this->render_partial('accordion', array('tabs' => $tabs), $return);
    }
}