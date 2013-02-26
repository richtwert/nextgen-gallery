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
        global $wpdb;

		$deactivator = $this->object->get_registry()->get_utility('I_NextGen_Deactivator');

		if ($this->is_post_request())
        {
            $params = $this->object->param('check_uninstall');

            if (!empty($params['deactivate']))
            {
                $deactivator->deactivate();
            }

            if (!empty($params['uninstall']))
            {
                $deactivator->uninstall();
                $deactivator->deactivate();
            }

            // to the plugins page so they can deactivate nextgen themselves or see that it's been deactivated already
            $url = get_admin_url() . 'plugins.php';

            if (headers_sent())
            {
                echo "<meta http-equiv='refresh' content='0;URL=\"" . $url . "\"'/>";
            }
            else {
                wp_redirect($url);
            }

            throw new E_Clean_Exit();
		}

        $this->object->render_partial(
            'deactivator_check_uninstall',
            array(
                'plugins_url' => get_admin_url() . 'plugins.php',
                'deactivate_label' => _('Deactivate NextGEN'),

                'uninstall_label'       => _('Deactivate NextGEN and remove all data'),
                'uninstall_confirm'     => _('You are about to uninstall this plugin from WordPress.\nThis action is not reversible.\n\nChoose [Cancel] to Stop, [OK] to Uninstall.\n'),

                'uninstall_warning_2' => _('WARNING:'),
                'uninstall_warning_3' => _('Removing your data cannot be undone.'),

                'uninstall_tables_desc' => _('When choosing to remove data you should first make a database backup of the following tables:'),
                'uninstall_tables'      => array($wpdb->nggpictures, $wpdb->nggalbum, $wpdb->nggallery),
            )
        );
    }

}
