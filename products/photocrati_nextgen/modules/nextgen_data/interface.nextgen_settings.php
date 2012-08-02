<?php

interface I_NextGen_Settings
{
	// Ensure that the implementing class is a singleton
	function get_instance($context = False);
}
