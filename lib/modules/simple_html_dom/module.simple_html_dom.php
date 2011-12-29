<?php

/***
	{
		Module: photocrati-simple_html_dom
	}
***/

include_once('simplehtmldom/simple_html_dom.php');
class M_Simple_Html_Dom extends C_Base_Module
{
    function initialize()
    {
        parent::initialize(
        		'photocrati-simple_html_dom',
            'Simple HTML Dom',
            'Provides the simple_html_dom utility for other modules to use',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
}

new M_Simple_Html_Dom();
