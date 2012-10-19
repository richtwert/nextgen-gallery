<?php

class A_NextGen_Basic_Template_Resources extends Mixin
{
	function initialize()
	{
		$this->object->add_pre_hook(
			'enqueue_backend_resources',
			'Enqueues resources required for NextGEN template widget',
			__CLASS__,
			'enqueue_nextgen_basic_template_resources'
		);
	}

	function enqueue_nextgen_basic_template_resources()
	{
		wp_enqueue_script(
            'ngg_template_settings',
            $this->static_url('/js/ngg_template_settings.js'),
            array('jquery-ui-autocomplete')
        );

		// feed our autocomplete widget a list of available files
        $files_list = array();
        $template_locator = $this->object->get_registry()->get_utility('I_Legacy_Template_Locator');
        $files_available = $template_locator->find_all();
        foreach ($files_available as $label => $files)
        {
            foreach ($files as $file) {
                $tmp = explode(DIRECTORY_SEPARATOR, $file);
                $files_list[] = "[{$label}]: " . end($tmp);
            }
        }

		wp_localize_script(
			'ngg_template_settings',
			'nextgen_settings_templates_available_files',
			$files_list
		);
	}



}