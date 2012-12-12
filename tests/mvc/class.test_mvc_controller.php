<?php

require_once(path_join(NEXTGEN_GALLERY_TESTS_DIR, 'class.test_component_base.php'));
require_once('mocks.php');

class C_Test_MVC_Controller extends C_Test_Component_Base
{
	/**
	 * Registers a route with the MVC Router
	 */
	function setUp()
	{
		parent::setUp();

		// Adds a route to the router that will pass requests starting with
		// "/automated_test_route" to an instance of the
		// C_Test_MVC_Mock_Controller.
		//
		// If the URI is "/automated_test_route/index", the index() method is
		// invoked for the controller instance. In this example, "index" is
		// called the request "action".
		//
		// If no action is specified, "index" is assumed
		$this->named_route = 'automated_test_route';
		$this->get_router()->add_route(
			$this->named_route,
			'C_Test_MVC_Mock_Controller',
			array(
				'uri'	=>	$this->get_router()->routing_pattern($this->named_route)
			)
		);
		$this->assertNotNull($this->get_router()->get_named_route($this->named_route));
	}


	function test_controller_routing()
	{
		// Fetch the default index page by simulating a request. No custom
		// index() action has been defined for the test controller yet,
		// so the default index page should be displayed
		$uri = $_SERVER['REQUEST_URI'];

		// We're going to test three routes, which should yield the same output:
		$routes = array(
			"/{$this->named_route}",
			"/{$this->named_route}/",
			"/{$this->named_route}/index",
		);

		// Simulate the routing request
		foreach ($routes as $route) {
			$contents = $this->request_route($route);

			// Assert assumptions
			$this->assertTrue(
				strlen($contents) > 0,
				"When making a request for {$_SERVER['REQUEST_URI']}, no default
				template for the index action was served."
			);

			$this->assertTrue(
				strpos($contents, 'default') !== FALSE,
				"When making a request for {$_SERVER['REQUEST_URI']}, content was
				served, but not the default template for the index action."
			);
		}

		// Register adapter for Test_MVC_Controller
		$this->get_registry()->add_adapter('I_Test_MVC_Controller', 'Mixin_Override_Mock_Index');

		// Perform the request
		$response = $this->request_route("/{$this->named_route}");

		// Assure that the new index action is used
		$this->assertEqual($response, 'Hello');

		$_SERVER['REQUEST_URI'] = $uri;
	}


	/**
	 * Tests routing a custom action called 'foobar'
	 */
	function test_custom_action()
	{
		$uri = $_SERVER['REQUEST_URI'];
		$response = $this->request_route("/{$this->named_route}/foobar");
		$this->assertEqual($response, 'Foo Bar');
		$_SERVER['REQUEST_URI'] = $uri;
	}


	/**
	 * Removes the route registered with the MVC Router
	 */
	function tearDown()
	{
		$this->get_router()->remove_route($this->named_route);
		$this->assertNull($this->get_router()->get_named_route($this->named_route));
	}


	/**
	 * Gets the router instance
	 * @return C_Router
	 */
	function get_router()
	{
		return $this->get_registry()->get_utility('I_Router');
	}


	/**
	 * Simulates a request for a particular route, and returns the response
	 * @param string $route
	 * @return string
	 */
	function request_route($route)
	{
		ob_start();
		$_SERVER['REQUEST_URI'] = $route;
		$this->get_router()->route(FALSE);
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
