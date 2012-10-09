<?php

interface I_NextGen_Deactivator
{
    function deactivate();
    function define($context);
    function flush_cache();
    function get_instance($context = False);
    function uninstall();
}
