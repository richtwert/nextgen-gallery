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
            $factory = $this->object->get_registry()->get_utility('I_Component_Factory');
            $displayed_gallery = $factory->create('displayed_gallery', $mapper, json_decode($params));
        }

        // Provides settings fields and frontend rendering
        $this->get_registry()->add_adapter(
            'I_Display_Type_Controller',
            'A_NextGen_Basic_Thumbnails_Controller',
            $this->module_id
        );

        var_dump(stripslashes($params));
        var_dump(json_decode(stripslashes(urldecode($params))));
        var_dump(json_decode(stripslashes($params)));

        var_dump(json_last_error());

        $tmp = $this->object->get_registry()->get_utility('I_Display_Type_Controller');
        $tmp->index($displayed_gallery);
        exit;

        $retval['test'] = stripslashes($params);
        $retval['params'] = json_decode(stripslashes($params));
        $retval['displayed_gallery'] = $displayed_gallery;

        return $retval;

    }
}
