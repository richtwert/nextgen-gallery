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

        $parent      = C_Component_Registry::get_instance()->get_utility('I_Widget');
        $image_map   = C_Component_Registry::get_instance()->get_utility('I_Image_Mapper');
        $gallery_map = C_Component_Registry::get_instance()->get_utility('I_Gallery_Mapper');

        // if there's fewer images than our maximum, lower the # to display
        $count = $image_map->count();
        if ($count < $instance['items'])
            $instance['items'] = $count;

        // begin building our query
        $query = $image_map->select();

        // random display
        if ($instance['type'] == 'random')
        {
            $query->order_by('rand()');
            $query->limit($instance['items']);
        }
        else {
            // 'recent' display
            $query->order_by($image_map->get_primary_key_column(), 'DESC');
            $query->limit($instance['items'], 0);
        }

        // thanks to Kay Germer for the idea & addon code
        if ((!empty($instance['list'])) && ($instance['exclude'] != 'all'))
        {
            $instance['list'] = explode(',', $instance['list']);
            $instance['list'] = "'" . implode("', '", $instance['list']) . "'";

            if ($instance['exclude'] == 'denied')
                @$query->where("galleryid NOT IN ({$instance['list']})");

            if ($instance['exclude'] == 'allow')
                @$query->where("galleryid IN ({$instance['list']})");

            // Limit the output to the current author, can be used on author template pages
            if ($instance['exclude'] == 'user_id')
            {
                @$tmp = $gallery_map->select($gallery_map->get_primary_key_column())->where("author IN ({$instance['list']})")->run_query();
                $gallery_ids = array();
                foreach ($tmp as $t) {
                    $gallery_ids[] = $t->{$gallery_map->get_primary_key_column()};
                }
                $gallery_ids = implode(',', $gallery_ids);
                @$query->where("galleryid IN ({$gallery_ids})");
            }
        }

        $image_list = $query->run_query();

        // IE8 webslice support if needed
        if ($instance['webslice'])
        {
            $before_widget .= '<div class="hslice" id="ngg-webslice">';
            $before_title  = str_replace('class="' , 'class="entry-title ', $before_title);
            $after_widget  = '</div>' . $after_widget;
        }

        $parent->render_partial(
            'widget#display_gallery',
            array(
                'self'       => $this,
                'instance'   => $instance,
                'title'      => $title,
                'image_list' => $image_list,
                'before_widget' => $before_widget,
                'before_title'  => $before_title,
                'after_widget'  => $after_widget,
                'after_title'   => $after_title,
                'widget_id'     => $widget_id
            )
        );
    }
}
