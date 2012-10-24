<?php

/**
 * Provides an interface to identify the test controller
 */
interface I_Test_MVC_Controller
{

}

/**
 *  Provides a mock MVC controller used to test with
 */
class C_Test_MVC_Mock_Controller extends C_MVC_Controller
{
	function define()
	{
		parent::define();
		$this->implement('I_Test_MVC_Controller');
	}

	function foobar_action()
	{
		$view = implode(
			DIRECTORY_SEPARATOR,
			array(dirname(__FILE__), 'templates', 'foobar_template.tmpl')
		);

        $this->render_view(
            $view,
            array(
                'foobar' => 'Foo Bar'
            )
        );
    }
}

class Mixin_Override_Mock_Index extends Mixin
{
	function index_action()
	{
		echo "Hello";
		return;
	}
}
