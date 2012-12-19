<?php

class A_Routing_App_Factory extends Mixin
{
    function routing_app($context = FALSE)
    {
        return new C_Routing_App($context);
    }
}
