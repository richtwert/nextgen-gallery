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
        $files_available = $this->object->get_available_templates();
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


    /**
     * Returns an array of template storing directories
     *
     * @return array Template storing directories
     */
    function get_template_directories()
    {
        return array(
            'Overrides' => STYLESHEETPATH . DIRECTORY_SEPARATOR . 'nggallery' . DIRECTORY_SEPARATOR,
            'NextGen' => NGGALLERY_ABSPATH . 'view' . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Returns an array of all available template files
     *
     * @return array All available template files
     */
    function get_available_templates($prefix = FALSE)
    {
        $files = array();
        foreach ($this->object->get_template_directories() as $label => $dir) {
            $tmp = $this->object->get_templates_from_dir($dir, $prefix);
            if (!$tmp) { continue; }
            $files[$label] = $tmp;
        }
        return $files;
    }

    /**
     * Recursively scans $dir for files ending in .php
     *
     * @param string $dir Directory
     * @return array All php files in $dir
     */
    function get_templates_from_dir($dir, $prefix = FALSE)
    {
        if (!is_dir($dir))
        {
            return;
        }

        $dir = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir);

        // convert single-item arrays to string
        if (is_array($prefix) && count($prefix) <= 1)
        {
            $prefix = end($prefix);
        }

        // we can filter results by allowing a set of prefixes, one prefix, or by showing all available files
        if (is_array($prefix))
        {
            $str = implode('|', $prefix);
            $regex_iterator = new RegexIterator($iterator, "/({$str})-.+\.php$/i", RecursiveRegexIterator::GET_MATCH);
        }
        elseif (is_string($prefix))
        {
            $regex_iterator = new RegexIterator($iterator, "/{$prefix}-.+\.php$/i", RecursiveRegexIterator::GET_MATCH);
        }
        else {
            $regex_iterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        }

        $files = array();
        foreach ($regex_iterator as $filename) {
            $files[] = reset($filename);
        }

        return $files;
    }
}