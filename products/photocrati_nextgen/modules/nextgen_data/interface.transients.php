<?php

interface I_Transients
{
    function get_instance($context = False);

    function get_value($name);
    function set_value($name, $value, $expiration);
}
