<?php

class A_Display_Validation_Errors extends Mixin
{
	function show_errors_for($entity, $return=FALSE)
	{
		$retval = '';

		if ($entity->is_invalid()) {
			$retval = $this->object->render_partial('entity_errors', array(
				'entity'	=>	$entity
			), $return);
		}

		return $retval;
	}
}