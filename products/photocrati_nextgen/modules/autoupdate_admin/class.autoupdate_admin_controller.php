<?php

// <XXX temporary
interface I_Admin_Controller 
{

}

class C_MVC_Controller extends C_Component
{
	function render_partial($template, $vars)
	{
		$dir = realpath(dirname(__FILE__));
		$path = $dir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.php';
		$real = realpath($path);
		
		if (file_exists($path) && dirname(dirname($real)) == $dir) {
			extract($vars);
			
			include($path);
		}
	}
}
// XXX> 

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
