<?php

class A_NextGen_Basic_Singlepic extends Mixin
{
    function initialize()
    {
        if ($this->object->name == 'photocrati-nextgen_basic_singlepic')
        {
            $this->object->add_pre_hook('validation',   get_class(), 'Hook_NextGen_Basic_Singlepic_Validation');
            $this->object->add_pre_hook('set_defaults', get_class(), 'Hook_NextGen_Basic_Singlepic_Validation');
        }
    }
}

class Hook_NextGen_Basic_Singlepic_Validation extends Hook
{
    function set_defaults()
    {
        // Set defaults
        if (!isset($this->object->settings))
            $this->object->settings = array();
        if (!isset($this->object->settings['width']))
            $this->object->settings['width'] = '';
        if (!isset($this->object->settings['height']))
            $this->object->settings['height'] = '';
        if (!isset($this->object->settings['mode']))
            $this->object->settings['mode'] = '';
        if (!isset($this->object->settings['display_watermark']))
            $this->object->settings['display_watermark'] = '';
        if (!isset($this->object->settings['display_reflection']))
            $this->object->settings['display_reflection'] = '';
        if (!isset($this->object->settings['float']))
            $this->object->settings['float'] = '';
        if (!isset($this->object->settings['link']))
            $this->object->settings['link'] = '';
        if (!isset($this->object->settings['quality']))
            $this->object->settings['quality'] = '100';
        if (!isset($this->object->settings['crop']))
            $this->object->settings['crop'] = '';
        if (!isset($this->object->settings['template']))
            $this->object->settings['template'] = '';
    }

    function validation()
    {
        $this->object->validates_numericality_of('image_id');
    }
}
