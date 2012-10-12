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
            'form_gallery',
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

        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->nggpictures} WHERE exclude != 1");
        if ($count < $instance['items'])
            $instance['items'] = $count;

        $exclude_list = '';

        // Thanks to Kay Germer for the idea & addon code
        if ((!empty($instance['list'])) && ($instance['exclude'] != 'all'))
        {
            $instance['list'] = explode(',', $instance['list']);

            // Prepare for SQL
            $instance['list'] = "'" . implode("', '", $instance['list']) . "'";

            if ($instance['exclude'] == 'denied')
                $exclude_list = "AND NOT (t.gid IN ({$instance['list']}))";

            if ($instance['exclude'] == 'allow')
                $exclude_list = "AND t.gid IN ({$instance['list']})";

            // Limit the output to the current author, can be used on author template pages
            if ($instance['exclude'] == 'user_id')
                $exclude_list = "AND t.author IN ({$instance['list']})";
        }

        if ($instance['type'] == 'random')
            $image_list = $wpdb->get_results("SELECT t.*, tt.* FROM {$wpdb->nggallery} AS t INNER JOIN {$wpdb->nggpictures} AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 {$exclude_list} ORDER BY RAND() LIMIT {$instance['items']}");
        else
            $image_list = $wpdb->get_results("SELECT t.*, tt.* FROM {$wpdb->nggallery} AS t INNER JOIN {$wpdb->nggpictures} AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 {$exclude_list} ORDER BY pid DESC LIMIT 0, {$instance['items']}");

        // IE8 webslice support if needed
        if ($instance['webslice'])
        {
            $before_widget .= "\n" . '<div class="hslice" id="ngg-webslice">' . "\n";
            //the headline needs to have the class enty-title
            $before_title  = str_replace('class="' , 'class="entry-title ', $before_title);
            $after_widget  =  '</div>'."\n" . $after_widget;
        }

        echo $before_widget . $before_title . $title . $after_title;
        echo "\n" . '<div class="ngg-widget entry-content">' . "\n";

        if (is_array($image_list))
        {
            foreach ($image_list as $image) {
                // get the URL constructor
                $image = new nggImage($image);

                // get the effect code
                $thumbcode = $image->get_thumbcode($widget_id);

                // enable i18n support for alttext and description
                $alttext     = htmlspecialchars(stripslashes(nggGallery::i18n($image->alttext, 'pic_' . $image->pid . '_alttext')));
                $description = htmlspecialchars(stripslashes(nggGallery::i18n($image->description, 'pic_' . $image->pid . '_description')));

                // TODO: For mixed portrait/landscape it's better to use only the height setting, if width is 0 or vice versa
                $out = '<a href="' . $image->imageURL . '" title="' . $description . '" ' . $thumbcode . '>';

                // Typo fix for the next updates (happend until 1.0.2)
                $instance['show'] = ($instance['show'] == 'orginal') ? 'original' : $instance['show'];

                if ($instance['show'] == 'original')
                    $out .= '<img src="' . trailingslashit(home_url()) . 'index.php?callback=image&amp;pid=' . $image->pid . '&amp;width=' . $instance['width'] . '&amp;height=' . $instance['height'] . '" title="' . $alttext . '" alt="' . $alttext . '"/>';
                else
                    $out .= '<img src="' . $image->thumbURL . '" width="' . $instance['width'] . '" height="' . $instance['height'] . '" title="' . $alttext . '" alt="' . $alttext . '"/>';

                echo $out . '</a>' . "\n";
            }
        }

        echo '</div>';
        echo $after_widget;
    }

}
