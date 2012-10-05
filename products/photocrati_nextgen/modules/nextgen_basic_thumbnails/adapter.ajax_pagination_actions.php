<?php

class A_Ajax_Pagination_Actions extends Mixin
{
    function get_displayed_gallery_page_action()
    {
        $retval = array();
        $mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

        if (($id = $this->object->param('displayed_gallery_id')))
        {
            // retrieve by transient id
            $factory           = $this->object->get_registry()->get_utility('I_Component_Factory');
            $transient_key     = 'dg_'.$id;
            $displayed_gallery = $factory->create('displayed_gallery', $mapper);
            $displayed_gallery->apply_transient($transient_key);

            // render the displayed gallery
            $this->renderer                 = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
            $retval['html']                 = $this->renderer->render_displayed_gallery($displayed_gallery, TRUE);
            $retval['displayed_gallery_id'] = $displayed_gallery->id();
        }
        return $retval;
    }
}
