<?php

class Hook_NextGen_Basic_Gallery_Integration extends Hook
{
    function index_action($displayed_gallery, $return=FALSE)
    {
        // Are we to display a different display type?
        if (($show = $this->object->param('show'))) {
            if ($show != $this->object->context) {
                
                // We've got an alternate request. We'll use a different display
                // type to serve the request and not run the original controller
                // action
                $this->object->set_method_property(
                    $this->method_called,
                    ExtensibleObject::METHOD_PROPERTY_RUN,
                    FALSE
                );
                
                // Render the new display type
                $renderer = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
                $displayed_gallery->original_display_type = $displayed_gallery->display_type;
                $displayed_gallery->display_type = $show;
                $retval = $renderer->display_images((array)$displayed_gallery->get_entity(), $return);
                
                // Set return value
                $this->object->set_method_property(
                    $this->method_called,
                    ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
                    $retval
                );
                
                return $retval;
            }
        }
    }
}