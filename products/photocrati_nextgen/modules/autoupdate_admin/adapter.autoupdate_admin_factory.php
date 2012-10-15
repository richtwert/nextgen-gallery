<?php

class A_AutoUpdate_Admin_Factory extends Mixin
{
    function autoupdate_admin_controller()
    {
        return new C_AutoUpdate_Admin_Controller();
    }
}
