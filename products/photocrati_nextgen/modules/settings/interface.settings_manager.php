<?php

interface I_Settings_Manager
{
	function get($property, $default=NULL);
	function set($property, $value);
	function is_set($property);
	function un_set($property);
	function load();
	function save();
	function set_defaults();
}
