<?php

class Mixin_WordPress_Security_Manager extends Mixin
{
	function get_actor($actor_id, $actor_type = null, $args = null)
	{
		if ($actor_type == null)
		{
			$actor_type = 'user';
		}
		
		$object = null;
		
		if ($actor_id != null)
		{
			switch ($actor_type)
			{
				case 'user':
				{
					$object = get_userdata($actor_id);
				
					if ($object == false)
					{
						$object = null;
					}
				
					break;
				}
				case 'role':
				{
					$object = get_role($actor_id);
				
					if ($object == false)
					{
						$object = null;
					}
				
					break;
				}
			}
		}
		
		if ($object != null)
		{
			$actor = new C_WordPress_Security_Actor($actor_type);
			$entity_props = array(
				'type' => $actor_type, 
				'id' => $actor_id,
			);
			
			$actor->set_entity($object, $entity_props);
			
			return $actor;
		}
		
		return $this->object->get_guest_actor();
	}
	
	function get_current_actor()
	{
		return $this->object->get_actor(get_current_user_id(), 'user');
	}
	
	function get_guest_actor()
	{
		$actor = new C_WordPress_Security_Actor('user');
		$entity_props = array(
			'type' => 'user'
		);
		
		$actor->set_entity(null, $entity_props);
		
		return $actor;
	}
}

class Mixin_WordPress_Security_Manager_Request extends Mixin
{
	function get_request_token($action_name, $args = null)
	{
		$token = new C_Wordpress_Security_Token();
		$token->init_token($action_name, $args);
		
		return $token;
	}
}

class C_WordPress_Security_Manager extends C_Security_Manager
{
    static $_instances = array();

    function define($context=FALSE)
    {
			parent::define($context);

			$this->add_mixin('Mixin_WordPress_Security_Manager');
			$this->add_mixin('Mixin_WordPress_Security_Manager_Request');
    }

    static function get_instance($context = False)
    {
			if (!isset(self::$_instances[$context]))
			{
					self::$_instances[$context] = new C_WordPress_Security_Manager($context);
			}

			return self::$_instances[$context];
    }
}
