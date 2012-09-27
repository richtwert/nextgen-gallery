<?php

/**
 * Provides a wp-admin page to manage NextGEN Deactivator
 */
class C_NextGen_Deactivator_Controller extends C_NextGen_Backend_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->add_mixin('Mixin_NextGen_Deactivator_Controller');
		$this->implement('I_NextGen_Deactivator_Controller');
	}

	/**
	 * Gets an instance of the controller
	 * @param string $context
	 * @return C_NextGen_Deactivator_Controller
	 */
	static function &get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = function_exists('get_called_class') ?
				get_called_class() : get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}

class Mixin_NextGen_Deactivator_Controller extends Mixin
{
	function index_action()
	{
		$deactivator = $this->object->get_registry()->get_utility('I_NextGen_Deactivator');

		if ($this->is_post_request()) {
            $params = $this->object->param('check_uninstall');
            if (!empty($params['deactivate']))
            {
                // deactivate stuff
            }

            if (!empty($params['uninstall']))
            {
                // do uninstall
                $deactivator->uninstall();
                throw new E_Clean_Exit();
            }

            var_dump($params);

            print "this is a post request<br/>";
            exit;
		}

        $this->object->render_partial(
            'check_uninstall',
            array(
                'deactivate_label'  => _('Only deactivate'),
                'uninstall_label'   => _('Remove all NextGEN data and deactivate the plugin'),
                'uninstall_warning' => _('Deactivating NextGEN will leave your data intact. Choose "uninstall" to remove your galleries, albums, etc.')
            )
        );
    }


}

	/**
	 * Processes the POST request
	 * @param C_NextGen_Deactivator $deactivator
	 */
	function _process_post_request($deactivator)
	{
	}

}
