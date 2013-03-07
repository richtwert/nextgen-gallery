<?php

class A_Reset_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Reset & Uninstall';
	}

	function render()
	{
		return $this->object->render_partial(
            'nextgen_other_options#reset_tab',
            array(
                'reset_value'			=> _('Reset all options to default settings'),
                'reset_warning'			=> _('Replace all existing options and gallery options with their default settings'),
                'reset_label'			=> _('Reset settings'),
                'reset_confirmation'	=> _("Reset all options to default settings?\n\nChoose [Cancel] to Stop, [OK] to proceed."),
                'uninstall_label'		=> _('Deactivate & Uninstall'),
				'uninstall_confirmation'=>_("Completely remove NextGEN Gallery (delete galleries, tables, etc)?\n\nChoose [Cancel] to Stop, [OK] to proceed."),
            ),
            TRUE
        );
	}

	function reset_action()
	{
		$installer = $this->get_registry()->get_utility('I_Installer');
		$installer->uninstall();
		$installer->install();
	}

	function uninstall_action()
	{
		$installer = $this->get_registry()->get_utility('I_Installer');
		$installer->uninstall(TRUE);
		deactivate_plugins(NEXTGEN_GALLERY_PLUGIN_BASENAME);
		wp_redirect(admin_url('/plugins.php'));
	}
}