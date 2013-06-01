<?php

class A_Miscellaneous_Form extends Mixin
{
	function get_model()
	{
		return $this->get_registry()->get_utility('I_Settings_Manager');
	}

	function get_title()
	{
		return 'Miscellaneous';
	}

	function render()
	{
		return $this->object->render_partial(
            'nextgen_other_options#misc_tab',
            array(
                'mediarss_activated'       => $this->object->get_model()->useMediaRSS,
                'mediarss_activated_label' => _('Add MediaRSS link?'),
                'mediarss_activated_help'  => _('When enabled, adds a MediaRSS link to your header. Third-party web services can use this to publish your galleries'),
                'mediarss_activated_no'    => _('No'),
                'mediarss_activated_yes'   => _('Yes'),

                'cache_label'        => _('Clear image cache'),
                'cache_confirmation' => _("Completely clear the NextGEN cache of all image modifications?\n\nChoose [Cancel] to Stop, [OK] to proceed."),

                 'slug_field' => $this->_render_text_field(
                     (object)array('name' => 'misc_settings'),
                     'router_param_slug',
                     'Permalink slug',
                     $this->object->get_model()->router_param_slug
                 )
            ),
            TRUE
        );
	}

    function cache_action()
    {
        $cache   = $this->get_registry()->get_utility('I_Cache');
        $manager = $this->get_registry()->get_utility('I_Resource_Manager');

        $cache->flush_galleries();
        $manager->flush_cache();
    }

	function save_action()
	{
		if (($settings = $this->object->param('misc_settings')))
        {
			$this->object->get_model()->set($settings)->save();
		}
	}
}