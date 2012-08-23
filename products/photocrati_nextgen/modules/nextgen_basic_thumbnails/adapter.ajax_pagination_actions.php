<?php

class A_Ajax_Pagination_Actions extends Mixin
{
    function get_page_action()
    {
        $retval = array();

        $displayed_gallery = NULL;
        $mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

        // Create the displayed gallery, based on the parameters
        if (($params = $this->object->param('params')))
        {
            $params = json_decode(stripslashes($params));
            $factory = $this->object->get_registry()->get_utility('I_Component_Factory');
            $displayed_gallery = $factory->create('displayed_gallery', $mapper, $params);
        }

        // Display!
        $displayed_gallery->id(uniqid('temp'));
        $controller = $this->get_registry()->get_utility('I_Display_Type_Controller', $displayed_gallery->display_type);
        $controller->enqueue_frontend_resources($displayed_gallery);
        $controller->index($displayed_gallery);

        $retval['test'] = 'test';

        return $retval;

    }
}
