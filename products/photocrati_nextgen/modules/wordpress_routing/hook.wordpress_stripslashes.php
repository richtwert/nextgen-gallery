<?php

class Hook_WordPress_Stripslashes extends Hook
{
    /**
     * Wordpress has issues with adding escape codes even when PHP's magic quotes are off..
     */
    function get_parameter($key, $id = NULL, $default = NULL, $segment = FALSE, $url = FALSE)
    {
        $this->object->set_method_property(
            $this->method_called,
            ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
            stripslashes_deep(
                $this->object->get_method_property(
                    $this->method_called,
                    ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
                )
            )
        );
    }
}