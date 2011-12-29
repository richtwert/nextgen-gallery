<?php

class A_MVC_Controller_Helpers extends Mixin
{
    function show_errors_for($active_record)
    {
        $this->object->render_partial(
            "active_record_errors",
            array('record' => $active_record)    
        );
    }
}