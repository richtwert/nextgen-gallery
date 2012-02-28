<?php

class C_Ajax_Handler extends C_MVC_Controller
{   
    function define()
    {
        parent::define();
        $this->implement('I_Ajax_Handler');
    }
    
    function index()
    {
				ob_start();
				
        $retval = array('error' => 'Action does not exist');
        
        if ($this->param('action') && $this->has_method($this->param('action'))) {
            $action = $this->param('action');
            $retval = $this->$action();
        }
				
				while (ob_get_level() > 0)
				{
					ob_end_clean();
				}
				
        // Needed by CGI
        header('Content-Type: application/json');
        flush();
        
        // Output the JSON
        echo json_encode($retval);
        
        exit();
    }
}
