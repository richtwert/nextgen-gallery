<?php

class A_Ajax_Pagination_Actions extends Mixin
{
    function get_displayed_gallery_page_action()
    {
        $retval = array();
        $mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

        if (($id = $this->object->param('displayed_gallery_id')))
        {
            $app = $this->object->get_registry()->get_utility('I_Router')->get_routed_app();
            $app->remove_parameter('action');
            $app->remove_parameter('displayed_gallery_id');
            $app->remove_parameter('page');

            // retrieve by transient id
            $factory           = $this->object->get_registry()->get_utility('I_Component_Factory');
            $displayed_gallery = $factory->create('displayed_gallery', $mapper);
            $displayed_gallery->apply_transient($id);

            // render the displayed gallery
            $this->renderer                 = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
            $retval['html']                 = $this->renderer->render_displayed_gallery($displayed_gallery, TRUE);
            $retval['displayed_gallery_id'] = $displayed_gallery->id();
        }
        return $retval;
    }
}
