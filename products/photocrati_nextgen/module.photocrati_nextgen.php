<?php

/***
	{
		Module: photocrati-nextgen
	}
***/

class P_Photocrati_NextGen extends C_Base_Product
{
    function define()
    {
        parent::define(
        		'photocrati-nextgen',
            'Photocrati NextGen',
            'Photocrati NextGen',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
}

new P_Photocrati_NextGen();
