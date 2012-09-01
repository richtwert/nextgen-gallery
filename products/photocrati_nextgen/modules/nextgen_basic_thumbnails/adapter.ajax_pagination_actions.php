<?php

class A_Ajax_Pagination_Actions extends Mixin
{
    function get_page_action()
    {
        $displayed_gallery = NULL;
        $mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

        if ($transient_id = $this->object->param('transient_id'))
        {
            // retrieve by transient id
            $transient_handler = $this->object->get_registry()->get_utility('I_Transients');
            $factory           = $this->object->get_registry()->get_utility('I_Component_Factory');
            $transient = $transient_handler->get_value('displayed_gallery_' . $transient_id);
            $displayed_gallery = $factory->create(
                'displayed_gallery', $mapper, $transient
            );
        }

        // Display!
        ob_start();
        $controller = $this->get_registry()->get_utility('I_Display_Type_Controller', $displayed_gallery->display_type);
        $controller->enqueue_frontend_resources($displayed_gallery);
        $controller->index($displayed_gallery);
        $output = ob_get_contents();
        ob_end_clean();

        print $output;
        exit;
    }
}
