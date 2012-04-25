<?php

class A_Admin_Factory extends Mixin
{
    function admin_controller()
    {
        return new C_Photocrati_Admin();
    }
}