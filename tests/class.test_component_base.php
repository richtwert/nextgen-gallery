<?php

/**
 * Provides a means for subclasses to test components
 */
abstract class C_Test_Component_Base extends UnitTestCase
{
	/**
	 * Provides a convenience method for getting a factory object
	 * @return C_Component_Factory
	 */
	function get_factory()
	{
		return $this->get_registry()->get_singleton_utility('I_Component_Factory');
	}

	/**
	 * Provides a convenience method for getting the component registry
	 * @return C_Component_Registry
	 */
	function get_registry()
	{
		return C_Component_Registry::get_instance();
	}


	/************ ASSERTION HELPERS ******************************************/
	function assertEmpty($object, $message='%s')
	{
		return !$this->assertNotEmpty($object, $message);
	}

	function assertNotEmpty($object, $message='%s')
	{
		$failed = TRUE;
		if (!is_null($object)) {
			switch (gettype($object)) {
				case 'int':
					if ($object > 0) $failed = FALSE;
					break;
				case 'string':
					if (strlen($object) > 0) $failed = FALSE;
					break;
				default:
					$failed = FALSE;
					break;
			}
		}

		if ($failed) $this->fail($message);
		else ($this->pass());
	}
}