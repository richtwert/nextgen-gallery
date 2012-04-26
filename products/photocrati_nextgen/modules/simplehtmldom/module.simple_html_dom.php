<?php

/***
	{
		Module: photocrati-simple_html_dom
	}
***/

if (!function_exists(('file_get_html'))) include_once('simplehtmldom/simple_html_dom.php');

class M_Simple_Html_Dom extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-simple_html_dom',
            'Simple HTML Dom',
            'Provides the simple_html_dom utility for other modules to use',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    
    function initialize()
    {
    }
}

new M_Simple_Html_Dom();