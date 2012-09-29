<?php

class A_NextGen_Basic_Album_Controller extends Mixin
{

    function initialize()
    {
        $this->object->add_mixin('Mixin_NextGen_Basic_Templates');
    }

    /**
     * Renders the front-end for the NextGen Basic Album display type
     * @param $displayed_gallery
     * @param bool $return
     */
    function index_action($displayed_gallery, $return=FALSE)
    {
        // Are we to display a sub-album
        if (($album    = get_query_var('album'))) {
            $displayed_gallery->entity_ids = array();
            $displayed_gallery->container_ids = $album === '0' OR $album === 'all' ? array() : array($album);

        }
        elseif ($gallery  = get_query_var('gallery')) {
            // TODO: How do we display a gallery? It would encompass alternative-reviews I would think. *shrugs*
        }

        // Get all settings required for displaying this album
        $display_settings = $displayed_gallery->display_settings;
        $current_page = get_query_var('nggpage') ? get_query_var('nggpage') : (isset($_GET['nggpage']) ? intval($_GET['nggpage']) : 1);
        $offset = $display_settings['galleries_per_page'] * ($current_page - 1);
        $total = $displayed_gallery->get_album_entity_count();
        $entities = $displayed_gallery->get_album_entities($display_settings['galleries_per_page'], $offset);

        // If there are entities to be displayed
        if ($entities) {

            //  Create pagination
            if ($display_settings['galleries_per_page'] && !$display_settings['disable_pagination']) {
                $pagination = new nggNavigation;
                $display_settings['pagination'] = $pagination->create_navigation(
                    $current_page,
                    $total,
                    $display_settings['galleries_per_page']
                );
            }

            // Add additional parameters
            $display_settings['current_page']			= $current_page;
            $display_settings['entities']               = &$entities;
        }

        // Display "no entities found" message
        else {

        }

    }
}