<?php

class A_NextGen_Basic_Album_Mapper extends Mixin
{
    /**
     * Adds a hook for setting default values
     */
    function initialize()
    {
        $this->object->add_post_hook(
            'set_defaults',
            'NextGen Basic Album Defaults',
            'Hook_NextGen_Basic_Album_Defaults',
            'set_defaults'
        );
    }
}


class Hook_NextGen_Basic_Album_Defaults extends Hook
{
    function set_defaults()
    {

    }
}