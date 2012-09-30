<?php

interface I_NextGen_Deactivator
{
    function define($context);
    function get_instance($context = False);
    function uninstall();
    function deactivate();
}
