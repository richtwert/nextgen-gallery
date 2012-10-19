<?php

class C_Backbone_Controller extends C_MVC_Controller
{
	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_Backbone_Controller');
	}
}

/**
 * Provides implementation for Backbone MVC controller
 */
class Mixin_Backbone_Controller extends Mixin
{
	/**
	 * Loads one or more Backbone views
	 * @param string|array $name
	 */
	function load_view($names, $template_params=array())
	{
		if (!is_array($names)) $names = array($name);
		$mapper = $this->object->get_registry()->get_utility('I_Backbone_View_Mapper');

		// Iterate over each view name and load it
		foreach ($names as $name) {

			// Find the view
			if (($view = $mapper->find_by_name($name))) {
				if ($view->depends) foreach ($view->depends as $dependency) {
					$this->object->load_view($dependency);
				}

				// Render template
				$params = $view->template_params
						? $this->array_merge_assoc(
								$view->template_params, $template_params
						  )
						: $template_params;
				$this->object->render_partial($view->template, $params);

				// Enqueue script
				wp_enqueue_script($name, $view->script_url);
			}
		}
	}
}