<?php

interface I_NextGen_Deactivator_Controller extends I_MVC_Controller
{
    function get_instance($context = FALSE);
	function index_action();
}
