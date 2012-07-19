<?php

class A_Display_Validation_Errors extends Mixin
{
	function show_errors_for($entity)
	{
		if ($entity->is_invalid()) {
			$this->object->render_partial('entity_errors', array(
				'entity'	=>	$entity
			));
		}
	}
}