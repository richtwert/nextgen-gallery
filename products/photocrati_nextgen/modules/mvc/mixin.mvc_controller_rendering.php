<?php

/**
 * Provides method used to render methods and get static content relative
 * to a module
 */
class Mixin_MVC_Controller_Rendering extends Mixin
{
    function set_content_type($type)
    {
        switch ($type) {
            case 'html':
            case 'xhtml':
                $type = 'text/html';
                break;
			case 'xml':
				$type = 'text/xml';
				break;
			case 'rss':
			case 'rss2':
				$type = 'application/rss+xml';
				break;
            case 'css':
                $type = 'text/css';
                break;
            case 'javascript':
            case 'jscript':
            case 'emcascript':
                $type = 'text/javascript';
                break;
			case 'json':
				$type = 'application/json';
				break;
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                $type = 'image/jpeg';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            case 'png':
                $type = 'image/x-png';
                break;
            case 'tiff':
            case 'tif':
                $type = 'image/tiff';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
        }

        $this->object->_content_type = $type;
        return $type;
    }


	/**
	 * Renders a template and outputs the response headers
	 * @param string $name
	 * @param array $vars
	 */
    function render_view($name, $vars=array())
    {
		$this->render();
        $this->object->render_partial($name, $vars);
    }


	/**
	 * Outputs the response headers
	 */
	function render()
	{
		if (!headers_sent()) header('Content-Type: '.$this->object->_content_type);
	}


    /**
     * Renders a view
     */
    function render_partial($__name, $__vars=array(), $__return=FALSE)
    {
        // If the template given is an absolute path, then use that - otherwise find the template
        $__filename = (strpos($__name, '/') === 0) ? $__name: $this->object->find_template($__name);
        ob_start();
        extract((array)$__vars);
        include($__filename);
        $__content = ob_get_clean();
        if ($__return)
            return $__content;
        else
            echo $__content;
    }

    /**
     * Finds and includes a template
     */
    function find_template($name)
    {
        $found		= FALSE;
		$settings	= $this->get_registry()->get_utility('I_Settings_Manager');

        $patterns = array(
            $this->object->get_class_definition_dir(),
            $this->object->get_class_definition_dir(TRUE),
            $settings->mvc_module_dir
        );

		$products = $this->get_registry()->get_product_list();

		foreach ($products as $product) {
			$module_path = $this->get_registry()->get_product_module_path($product);
			$patterns[] = path_join($module_path, '*');
		}

        foreach($patterns as $glob) {
            $found = glob(path_join($glob, "templates/{$name}.php"));
            if ($found) break;
        }

        if (!$found) throw new RuntimeException("{$name} is not a valid MVC template");

        return array_pop($found);
    }

	function get_router()
	{
		return $this->get_registry()->get_utility('I_Router');
	}
}
