<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


class Mixin_MVC_Controller_Defaults extends Mixin
{
    // Provide a default view
    function index($return=FALSE)
    {
        $this->debug = TRUE;
        return $this->render_partial('index', array(), $return);
    }
}


class Mixin_MVC_Controller_Rendering extends Mixin
{
    function set_content_type($type)
    {
        switch ($type) {
            case 'html':
            case 'xhtml':
                $type = 'text/html';
                break;
            case 'css':
                $type = 'text/css';
                break;
            case 'javascript':
            case 'jscript':
            case 'emcascript':
                $type = 'x-application/javascript';
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


    function render_view($name, $vars=array())
    {
        if (!headers_sent()) header('Content-Type: '.$this->object->_content_type);
        $this->object->render_partial($name, $vars);
    }


    /**
     * Renders a view
     */
    function render_partial($__name, $__vars=array(), $__return=FALSE)
    {
        // If the template given is an absolute path,
        // then use that - otherwise find the template
        $__filename = (strpos($__name, '/') === 0) ?
            $__name: $this->object->find_template($__name);
        $__content = '';
        ob_start();
        extract($__vars);
        include($__filename);
        $__content = ob_get_contents();
        ob_end_clean();

        if ($__return) return $__content;
        else echo $__content;
    }

    /**
     * Finds and includes a template
     */
    function find_template($name)
    {
        $found = FALSE;

        $patterns = array(
            $this->object->get_class_definition_dir(),
            $this->object->get_class_definition_dir(TRUE),
            MVC_MODULE_DIR
        );

				$products = $this->_get_registry()->get_product_list();

				foreach ($products as $product) {
					$module_path = $this->_get_registry()->get_product_module_path($product);
					$patterns[] = path_join($module_path, '*');
				}

        foreach($patterns as $glob) {
            $found = glob(path_join($glob, "templates/{$name}.php"));
            if ($found) break;
        }

        if (!$found) throw new RuntimeException("{$name} is not a valid MVC template");

        return array_pop($found);
    }


    /**
     * Finds a static resource
     * @param type $name
     * @return type
     */
    function find_static_file($name)
    {
        $found = FALSE;

        if (is_array($name)) $name = path_join($name);

        $patterns = array(
            $this->object->get_class_definition_dir(),
            MVC_MODULE_DIR
        );

				$products = $this->_get_registry()->get_product_list();

				foreach ($products as $product) {
					$module_path = $this->_get_registry()->get_product_module_path($product);
					$patterns[] = path_join($module_path, '*');
				}

        foreach($patterns as $glob) {
            $found = glob(path_join($glob, "static/{$name}"));
            if ($found) break;
        }

        return $found ? realpath((array_pop($found))) : NULL;
    }


    /**
     * Returns the URL to a static resource
     * @param mixed $resource, an array of paths, or a single path
     * @return string
     */
    function static_url($resource)
    {
        $path = $this->find_static_file($resource);
        return str_replace(
            realpath(PHOTOCRATI_GALLERY_PLUGIN_DIR),
            PHOTOCRATI_GALLERY_PLUGIN_URL,
            $path
        );
    }
}

/**
 * Provides actions that are executed based on the requested url
 */
abstract class C_MVC_Controller extends C_Component
{
    var $_content_type = 'text/html';
    var $_request = FALSE;
    var $_params = array();
    var $_request_method = "None";
    var $debug = FALSE;
	var $exit = FALSE;


    function define($context=FALSE)
    {
		parent::define($context);
        $this->add_mixin('Mixin_MVC_Controller_Defaults');
        $this->add_mixin('Mixin_MVC_Controller_Rendering');
        $this->implement('I_MVC_Controller');
    }

    function initialize()
    {
        parent::initialize();
        $this->_request = function_exists('apache_request_headers') ?
            apache_request_headers() : array();
        $this->_params = $this->parse_params($_REQUEST);
        $this->_request_method = $_SERVER['REQUEST_METHOD'];
    }


    function parse_params($arr)
    {
        $retval = array();

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = $this->parse_params($value);
            }
            elseif (is_string($value)) {
                if ($value == 'true') $value = TRUE;
                elseif ($value == 'false') $value = FALSE;
                elseif ($value == 'null') $value = NULL;
            }

            // Update the value
            $retval[$key] = $value;
        }

        return $retval;
    }


    function show_error($message, $code=500)
    {
        if (!headers_sent()) header("HTTP/1.0 {$code} {$message}");
        $this->render_view($code, array('message' => $message));
        if ($this->exit) throw new E_Clean_Exit();
    }

    function is_valid_request($method)
    {
        return TRUE;
    }


    function is_post_request()
    {
        return "POST" == $this->_request_method;
    }


    function is_get_request()
    {
        return "GET" == $this->_request_method;
    }


    function is_delete_request()
    {
       return "DELETE" == $this->_request_method;
    }


    function is_put_request()
    {
        return "PUT" == $this->_request_method;
    }


    function is_custom_request($type)
    {
        return strtolower($type) == strtolower($this->_request_method);
    }

    /**
     * Returns the value of a parameters
     * @param string $key
     * @return mixed
     */
    function &param($key, $default=NULL)
    {
        $retval = $default;

        if (isset($this->_params[$key])) {
            $val = &$this->_params[$key];
            if (is_array($val))
                $retval = &$val;
            elseif (is_string($val) && !in_array(strtolower($val), array('null','false')))
                $retval = &$val;
        }

        return $retval;
    }


    // Validates the request before executing an action. If no action has
    // been defined, then return 404
    function __call($method, $args) {
        $retval = '';
        if (strpos($method, 'action_') !== FALSE) {
            $action = preg_replace("/^action_/", '', $method);
            if ($this->is_valid_request($action)) {
                $throw = $this->_throw_error;
                $this->_throw_error = FALSE;
                if (method_exists($this, $action)) $this->$action();
                elseif (!parent::has_method($action)) {
                    $retval = $this->show_error("Page Not Found", 404);
                }
                else $retval = parent::__call ($action, $args);
                $this->_throw_error = $throw;
            }
        }
        else $retval = parent::__call ($method, $args);

        return $retval;
    }
}
