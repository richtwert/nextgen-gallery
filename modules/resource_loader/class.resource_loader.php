<?php

class Mixin_Resource_Loader extends Mixin
{
    function initialize()
    {
    	$this->call_parent();
    	
    	add_action('wp_print_footer_scripts', array($this, 'print_footer_scripts'));
    	add_action('wp_print_styles', array($this, 'print_styles'));
    }
    
    function enqueue_script($name, $dependency = FALSE, $anchor = FALSE, $version = FALSE)
    {
        $args = func_get_args();
        array_splice($args, 0, 4);
        
        if ($anchor == null) {
        	$anchor = $name . '_js';
        }
        
        $this->object->add_resource(
            'enqueued_script', $name, $anchor, $version,
            $dependency, array('args' => $args)
        );
    }
    
    function enqueue_stylesheet($name, $dependency = FALSE, $anchor = FALSE, $version = FALSE)
    {
        $args = func_get_args();
        array_splice($args, 0, 4);
        
        if ($anchor == null) {
        	$anchor = $name . '_css';
        }
        
        $this->object->add_resource(
            'enqueued_style', $name, $anchor, $version,
            $dependency, array('args' => $args)
        );
    }
    
    function add_resource($type, $name, $anchor, $version, $dependency, $properties)
    {
    	if ($dependency != null && !is_array($dependency)) {
    		$dependency = array($dependency);
    	}
    	
    	$resource_list = $this->object->resources;
    	
    	// XXX Leaving this as reminder and future debugging
#    	var_dump($type . ' - ' . $name);
#    	var_dump(isset($this->object->resources[$type]));
#    	var_dump(isset($resource_list[$type]));
#    	var_dump(count($resource_list));
#    	//var_dump($this->object->resources);
#    	//var_dump($resource_list);
    	
    	if (!isset($this->object->resources)) {
    		$this->object->resources = array();
    	}
    	
    	if (!isset($this->object->resources[$type])) {
    		$this->object->resources[$type] = array();
    	}
    	
    	if (isset($this->object->resources[$type][$name])) {
    		$resource = $this->object->resources[$type][$name];
    		$res_props = $resource['properties'];
    		$res_args = isset($res_props['args']) ? $res_props['args'] : null;
    		$new_args = isset($properties['args']) ? $properties['args'] : null;
    		
    		if ($res_args != $new_args) {
    			$index = 1;
    			while (isset($this->object->resources[$type][$name . '_' . $index])) {
    				$index++;
    			}
    			
    			$name = $name . '_' . $index;
    		}
    	}
    	else {
    		$object = null;
    		
			if ($type == 'enqueued_script') {
				global $wp_scripts;
				
				$object = $wp_scripts;
			}
			else if ($type == 'enqueued_style') {
				global $wp_styles;
				
				$object = $wp_styles;
			}
			
			if ($object != null && isset($object->registered[$name])) {
				$index = 1;
				while (isset($object->registered[$name . '_' . $index])) {
					$index++;
				}
			
				$name = $name . '_' . $index;
			}
    	}
    	
    	$this->object->resources[$type][$name] = array('name' => $name, 'type' => $type, 'anchor' => $anchor, 'version' => $version, 'dependency' => $dependency, 'properties' => $properties);
    	
    	if ($type == 'enqueued_script') {
    		wp_enqueue_script($name, $this->object->static_url('placeholder.js'), $dependency, $version, true);
    		
    		global $wp_scripts;
    		
    		$wp_scripts->add_data($name, 'photocrati-resource', true);
    	}
    	else if ($type == 'enqueued_style') {
    		wp_enqueue_style($name, $this->object->static_url('placeholder.css'), $dependency, $version);
    		
    		global $wp_styles;
    		
    		$wp_styles->add_data($name, 'photocrati-resource', true);
    	}
    }
    
    function print_footer_scripts()
    {
		$this->print_resource_list('enqueued_script');
    }
    
    function print_styles()
    {
		$this->print_resource_list('enqueued_style');
    }
    
    function print_resource_list($type)
    {
    	$object = null;
    	$property = null;
    	
    	if ($type == 'enqueued_script') {
    		global $wp_scripts;
    		
			$object = $wp_scripts;
			$property = 'in_footer';
    	}
    	else if ($type == 'enqueued_style') {
    		global $wp_styles;
    		
			$object = $wp_styles;
			$property = 'queue';
    	}
    	
    	if ($object != null) {
			foreach ($object->$property as $key => $handle) {
				if (!in_array($handle, $object->done, true)) {
					$dep = $object->registered[$handle];
					
					if (isset($dep->extra['photocrati-resource']) && 
						$dep->extra['photocrati-resource'] == true) {
						$resource = isset($this->object->resources[$type][$handle]) ? $this->object->resources[$type][$handle] : null;
					
						if ($resource != null) {
							$anchor = $resource['anchor'];
							$properties = $resource['properties'];
							$args = $properties['args'];
						
							if ($this->object->has_method($anchor)) {
								ob_start();
								call_user_func_array(array($this, $anchor), $args);
								$output = ob_get_clean();
								
								if ($type == 'enqueued_script') {
									$output =
										'<script type="text/javascript">' . "\n" .
											'//<![CDATA[' . "\n" . 
											$output . "\n" . 
											'//]]>' . "\n" .
										'</script>' . "\n";
								}
								else if ($type == 'enqueued_style') {
									$output =
										'<style type="text/css">' . "\n" .
											'/*<![CDATA[*/' . "\n" . 
											$output . "\n" . 
											'/*]]>*/' . "\n" .
										'</style>' . "\n";
								}
									
								if ($object->do_concat) {
									$object->print_html .= $output;
								}
								else {
									echo $output;
								}
							
								$object->done[] = $handle;
								
								if (isset($object->$property[$key])) {
									unset($object->$property[$key]);
								}
							}
						}
					}
				}
			}
    	}
    }
}


class C_Resource_Loader extends C_MVC_Controller
{   
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Resource_Loader');
        $this->implement('I_Resource_Loader');
    }
    
    function initialize($context = FALSE)
    {
        parent::initialize($context);
        if (!session_id()) session_start();
    }
}
