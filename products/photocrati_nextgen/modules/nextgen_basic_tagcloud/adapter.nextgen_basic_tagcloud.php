<?php

class A_NextGen_Basic_Tagcloud extends Mixin
{
    function initialize()
    {
        if ($this->object->name == 'photocrati-nextgen_basic_tagcloud')
        {
            $this->object->add_pre_hook('validation',   get_class(), 'Hook_NextGen_Basic_Tagcloud_Validation');
            $this->object->add_pre_hook('set_defaults', get_class(), 'Hook_NextGen_Basic_Tagcloud_Validation');
        }
    }
}

class Hook_NextGen_Basic_Tagcloud_Validation extends Hook
{
    function set_defaults()
    {
        // Set defaults
        if (!isset($this->object->settings))
            $this->object->settings = array();
        if (!isset($this->object->settings['display_type']))
            $this->object->settings['display_type'] = 'photocrati-nextgen_basic_thumbnails';
        if (!isset($this->object->settings['template']))
            $this->object->settings['template'] = '';
    }

    function validation()
    {
        $this->object->validates_presence_of('display_type');
    }
}
