<?php

class M_Active_Record extends C_Base_Module
{
    function __construct()
    {
        parent::__construct(
            'Active Record',
            'Provides the active record pattern for other modules to use',
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
}
new M_Active_Record();