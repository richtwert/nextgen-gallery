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
            $transient_handler = $this->object->get_registry()->get_utility('I_Transients');
            $factory           = $this->object->get_registry()->get_utility('I_Component_Factory');
            $transient_key     = 'dg_'.$id;
            if (($transient = $transient_handler->get_value($transient_key))) {
                $displayed_gallery = $factory->create(
                    'displayed_gallery', $mapper, $transient
                );

                // render the displayed gallery
                $this->renderer                 = $this->get_registry()->get_utility('I_Displayed_Gallery_Renderer');
                $retval['html']                 = $this->renderer->render_displayed_gallery($displayed_gallery, TRUE);
                $retval['displayed_gallery_id'] = $displayed_gallery->id();
            }
            else $retval['error'] = _('The following transient could not be found: ').$transient_key;
        }
        return $retval;
    }
}
