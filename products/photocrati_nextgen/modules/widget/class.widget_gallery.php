<?php

class C_Widget_Gallery extends WP_Widget
{
    function __construct()
    {
        $widget_ops = array('classname' => 'ngg_images', 'description' => __('Add recent or random images from the galleries', 'nggallery'));
        $this->WP_Widget('ngg-images', __('NextGEN Widget', 'nggallery'), $widget_ops);
    }

    function form($instance)
    {
        // used for rendering utilities
        $parent = C_Component_Registry::get_instance()->get_utility('I_Widget');

        // defaults
        $instance = wp_parse_args(
            (array)$instance,
            array(
                'exclude'  => 'all',
                'height'   => '50',
                'items'    => '4',
                'list'     =>  '',
                'show'     => 'thumbnail',
                'title'    => 'Gallery',
                'type'     => 'random',
                'webslice' => TRUE,
                'width'    => '75'
            )
        );

        $parent->render_partial(
            'widget#form_gallery',
            array(
                'self'     => $this,
                'instance' => $instance,
                'title'    => esc_attr($instance['title']),
                'items'    => intval($instance['items']),
                'height'   => esc_attr($instance['height']),
                'width'    => esc_attr($instance['width'])
            )
        );
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['items'] = (int)$new_instance['items'];
        $instance['type'] = $new_instance['type'];
        $instance['show'] = $new_instance['show'];
        $instance['width'] = (int)$new_instance['width'];
        $instance['height'] = (int)$new_instance['height'];
        $instance['exclude'] = $new_instance['exclude'];
        $instance['list'] = $new_instance['list'];
        $instance['webslice'] = (bool)$new_instance['webslice'];
        return $instance;
    }

    function widget($args, $instance)
    {
        // these are handled by extract() but I want to silence my IDE warnings that these vars don't exist
        $before_widget = NULL;
        $before_title = NULL;
        $after_widget = NULL;
        $after_title = NULL;
        $widget_id = NULL;

        extract($args);
        $title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title'], $instance, $this->id_base);

        $renderer  = C_Component_Registry::get_instance()->get_utility('I_Displayed_Gallery_Renderer');
        $factory   = C_Component_Registry::get_instance()->get_utility('I_Component_Factory');
        $dynthumbs = C_Component_Registry::get_instance()->get_utility('I_Dynamic_Thumbnails_Manager');
        $mapper    = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');
        $view      = $factory->create('mvc_view', '');

        $exclusions = array();

        if ((!empty($instance['list'])) && ($instance['exclude'] != 'all'))
        {
            if ($instance['exclude'] == 'denied')
                $exclusions = $instance['list'];
            if ($instance['exclude'] == 'allow')
                $container_ids = $instance['list'];
        }
        else {
            $container_ids = array();
            $galleries = $mapper->find_all();
            $key = $mapper->get_primary_key_column();
            foreach ($galleries as $gallery) {
                $container_ids[] = $gallery->$key;
            }
            $container_ids = implode(",", $container_ids);
        }

        if ($instance['type'] == 'random')
        {
            $order_by = 'rand()';
            $order_direction = '';
        }
        else if ($instance['type'] == 'recent')
        {
            $order_by = C_Component_Registry::get_instance()->get_utility('I_Image_Mapper')->get_primary_key_column();
            $order_direction = 'DESC';
        }

        // IE8 webslice support if needed
        if ($instance['webslice'])
        {
            $before_widget .= '<div class="hslice" id="ngg-webslice">';
            $before_title  = str_replace('class="' , 'class="entry-title ', $before_title);
            $after_widget  = '</div>' . $after_widget;
        }

        // they're probably a small dimension, so use the dynamic thumbnails manager to prevent squishing
        $thumbnail_size_name = $dynthumbs->get_size_name(array(
            'width'  => $instance['width'],
            'height' => $instance['height'],
            'crop'   => TRUE
        ));

        echo $renderer->display_images(array(
            'source' => 'galleries',
            'order_by' => $order_by,
            'order_direction' => $order_direction,
            'container_ids' => $container_ids,
            'exclusions' => $exclusions,
            'display_type' => NEXTGEN_GALLERY_BASIC_THUMBNAILS,
            'images_per_page' => $instance['items'],
            'maximum_entity_count' => $instance['items'],
            'template' => $view->get_template_abspath('widget#display_gallery'),
            'show_all_in_lightbox' => FALSE,
            'show_slideshow_link' => FALSE,
            'disable_pagination' => TRUE,
            'thumbnail_size_name'          => $thumbnail_size_name,
            'widget_setting_title'         => $title,
            'widget_setting_before_widget' => $before_widget,
            'widget_setting_before_title'  => $before_title,
            'widget_setting_after_widget'  => $after_widget,
            'widget_setting_after_title'   => $after_title,
            'widget_setting_width'         => $instance['width'],
            'widget_setting_height'        => $instance['height'],
            'widget_setting_show_setting'  => $instance['show'],
            'widget_setting_widget_id'     => $widget_id
        ));
    }
}
