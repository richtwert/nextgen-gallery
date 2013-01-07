<?php

class Mixin_NextGen_Basic_Thumbnail_Urls extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'set_parameter_value',
			'Ensure that the /page/2 parameter segment is always used',
			get_class(),
			'_set_ngglegacy_page_parameter'
		);
	}


	function create_parameter_segment($key, $value, $id, $use_prefix)
	{
		if ($key == 'page') {
			return 'page/'.$value;
		}
		else
			return $this->call_parent('create_parameter_segment', $key, $value, $id, $use_prefix);
	}

	function _set_ngglegacy_page_parameter()
	{
		// Get the returned url
		$retval		= $this->object->get_method_property(
			$this->method_called, ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// Create the regex pattern
		$sep		= preg_quote(MVC_PARAM_SEPARATOR, '#');
		if ($id)$id = preg_quote($id, '#').$sep;
		$prefix		= preg_quote(MVC_PARAM_PREFIX, '#');
		$regex		= implode('', array(
			'#/?/',
			$id ? "({$id})?" : '',
			"($prefix)?page{$sep}(\d+)/?#"
		));

		// Replace any page parameters with the ngglegacy equivalent
		if (preg_match($regex, $retval, $matches)) {
			$retval = str_replace($matches[0], "/page/{$matches[2]}/", $retval);
			$this->object->set_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				$retval
			);
		}

		return $retval;
	}
}