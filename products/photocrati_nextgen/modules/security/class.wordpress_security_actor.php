<?php

class Mixin_WordPress_Security_Actor extends Mixin
{
	function add_capability($capability_name)
	{
		$entity = $this->object->get_entity();
		
		if ($entity != null)
		{
			$capability_name = $this->object->get_native_action($capability_name);
			
			$entity->add_cap($capability_name);
			
			return true;
		}
		
		return false;
	}
	
	function remove_capability($capability_name)
	{
		$entity = $this->object->get_entity();
		
		if ($entity != null && $this->object->is_allowed($capability_name))
		{
			$capability_name = $this->object->get_native_action($capability_name);
			
			$entity->remove_cap($capability_name);
			
			return true;
		}
		
		return false;
	}
	
	function is_allowed($capability_name, $args = null)
	{
		$entity = $this->object->get_entity();
		
		if ($entity != null)
		{
			$capability_name = $this->object->get_native_action($capability_name, $args);
			
			return $entity->has_cap($capability_name);
		}
		
		return false;
	}
	
	function is_user()
	{
		return $this->object->get_entity_type() == 'user';
	}
	
	function get_native_action($capability_name, $args = null)
	{
		return $capability_name;
	}
}

class Mixin_WordPress_Security_Action_Converter extends Mixin
{
	function get_native_action($capability_name, $args = null)
	{
		switch ($capability_name)
		{
			case 'nextgen_edit_display_settings':
			{
				$capability_name = 'manage_options';
				
				break;
			}
		}
		
		return $capability_name;
	}
}

class C_WordPress_Security_Actor extends C_Security_Actor
{
	function define($context=FALSE)
	{
		parent::define($context);

		$this->add_mixin('Mixin_WordPress_Security_Actor');
		$this->add_mixin('Mixin_WordPress_Security_Action_Converter');
	}
}
