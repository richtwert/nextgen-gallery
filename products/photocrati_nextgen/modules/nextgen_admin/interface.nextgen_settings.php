<?php

interface I_Settings_Manager
{
	// Ensure that the implementing class is a singleton
	static function get_instance($context = False);
}
