<?php

class A_NextGen_Basic_Tagcloud_Controller extends Mixin
{
    function initialize()
    {
        $this->add_mixin('Mixin_NextGen_Basic_Tagcloud_Settings');
    }

    /**
     * Displays the 'tagcloud' display type
     *
     * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
     */
    function index_action($displayed_gallery, $return = FALSE)
    {
        global $nggRewrite;
        $display_settings = $displayed_gallery->display_settings;
        $tag = get_query_var('gallerytag');

        // we're looking at a tag, so show images w/that tag as a thumbnail gallery
        if ((get_query_var('pageid') == get_the_ID() || !is_home()) && !empty($tag))
        {
            $mapper  = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');
            $factory = $this->object->get_registry()->get_utility('I_Component_Factory');

            // recreate our gallery (so as to fill in the default settings)
            $dg = $displayed_gallery;
            $dg->display_type = $display_settings['display_type'];
            $dg->tag_ids = array(esc_attr($tag));
            $dg->container_ids = array(esc_attr($tag));
            $dg->source = 'tags';

            // and display it
            $dg = $factory->create('displayed_gallery', $mapper, $dg->get_entity());
            $controller = $this->object->get_registry()->get_utility('I_Display_Type_Controller', $dg->display_type);
            $controller->enqueue_frontend_resources($dg);
            return $controller->index_action($dg, $return);
        }

        $defaults = array(
            'exclude'  => '',
            'format'   => 'flat',
            'include'  => '',
            'largest'  => 22,
            'link'     => 'view',
            'number'   => 45,
            'order'    => 'ASC',
            'orderby'  => 'name',
            'smallest' => 8,
            'taxonomy' => 'ngg_tag',
            'unit'     => 'pt'
        );
        $args = wp_parse_args('', $defaults);

        // Always query top tags
        $tags = get_terms($args['taxonomy'], array_merge($args, array('orderby' => 'count', 'order' => 'DESC')));

        foreach ($tags as $key => $tag) {
            $tags[$key]->link = $nggRewrite->get_permalink(array('gallerytag' => $tag->slug));
            $tags[$key]->id = $tag->term_id;
        }

        $params = $display_settings;
        $params['inner_content'] = $displayed_gallery->inner_content;
        $params['storage']       = &$storage;
        $params['tagcloud']      = wp_generate_tag_cloud($tags, $args);
        return $this->object->render_partial('nextgen_basic_tagcloud', $params, $return);
    }

}