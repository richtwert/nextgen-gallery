<?php

class C_Ajax_Handler extends C_MVC_Controller
{
    function index()
    {
        $retval = array('error' => 'Action does not exist');
        
        if ($this->param('action') && $this->has_method($this->param('action'))) {
            $action = $this->param('action');
            $retval = $this->$action();
        }
        
        // Needed by CGI
        header('Content-Type: application/json');
        flush();
        
        // Output the JSON
        echo json_encode($retval);
    }
}