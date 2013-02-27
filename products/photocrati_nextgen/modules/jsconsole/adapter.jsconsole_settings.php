<?php

class A_JsConsole_Settings extends Mixin
{
	function initialize()
	{
		$this->object->set_default('jsconsole_enabled', FALSE);
		$this->object->set_default('jsconsole_session_key', '');
	}
}