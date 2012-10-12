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
        $title  = esc_attr($instance['title']);
        $items  = intval($instance['items']);
        $height = esc_attr($instance['height']);
        $width  = esc_attr($instance['width']);

        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :','nggallery'); ?>
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title');?>" type="text" class="widefat" value="<?php echo $title; ?>" />
            </label>
        </p>

        <p>
            <?php _e('Show :','nggallery'); ?><br />
            <label for="<?php echo $this->get_field_id('items'); ?>">
                <input style="width: 50px;" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items');?>" type="text" value="<?php echo $items; ?>" />
            </label>
            <select id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>" >
                <option <?php selected("thumbnail" , $instance['show']); ?> value="thumbnail"><?php _e('Thumbnails','nggallery'); ?></option>
                <option <?php selected("original" , $instance['show']); ?> value="original"><?php _e('Original images','nggallery'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>_random">
                <input id="<?php echo $this->get_field_id('type'); ?>_random" name="<?php echo $this->get_field_name('type'); ?>" type="radio" value="random" <?php checked("random" , $instance['type']); ?> /> <?php _e('random','nggallery'); ?>
            </label>
            <label for="<?php echo $this->get_field_id('type'); ?>_recent">
                <input id="<?php echo $this->get_field_id('type'); ?>_recent" name="<?php echo $this->get_field_name('type'); ?>" type="radio" value="recent" <?php checked("recent" , $instance['type']); ?> /> <?php _e('recent added ','nggallery'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('webslice'); ?>">
                <input id="<?php echo $this->get_field_id('webslice'); ?>" name="<?php echo $this->get_field_name('webslice'); ?>" type="checkbox" value="1" <?php checked(TRUE , $instance['webslice']); ?> /> <?php _e('Enable IE8 Web Slices','nggallery'); ?>
            </label>
        </p>

        <p>
            <?php _e('Width x Height :','nggallery'); ?><br />
            <input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /> x
            <input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" /> (px)
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Select :','nggallery'); ?>
                <select id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" class="widefat">
                    <option <?php selected("all" , $instance['exclude']); ?>  value="all" ><?php _e('All galleries','nggallery'); ?></option>
                    <option <?php selected("denied" , $instance['exclude']); ?> value="denied" ><?php _e('Only which are not listed','nggallery'); ?></option>
                    <option <?php selected("allow" , $instance['exclude']); ?>  value="allow" ><?php _e('Only which are listed','nggallery'); ?></option>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('list'); ?>"><?php _e('Gallery ID :','nggallery'); ?>
                <input id="<?php echo $this->get_field_id('list'); ?>" name="<?php echo $this->get_field_name('list'); ?>" type="text" class="widefat" value="<?php echo $instance['list']; ?>" />
                <br /><small><?php _e('Gallery IDs, separated by commas.','nggallery'); ?></small>
            </label>
        </p>
    <?php
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
