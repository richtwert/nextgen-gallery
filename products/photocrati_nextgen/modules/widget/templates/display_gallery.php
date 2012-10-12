<?php

echo $before_widget . $before_title . $title . $after_title;
echo '<div class="ngg-widget entry-content">';

if (is_array($image_list))
{
    foreach ($image_list as $image) {
        // get the URL constructor
        $image = new nggImage($image);

        // TODO: For mixed portrait/landscape it's better to use only the height setting, if width is 0 or vice versa
        $out = '<a href="' . $image->imageURL . '" title="' . $image->description . '" ' . $image->get_thumbcode($widget_id) . '>';

        if ($instance['show'] == 'original')
            $out .= '<img src="' . trailingslashit(home_url()) . 'index.php?callback=image&amp;pid=' . $image->pid . '&amp;width=' . $instance['width'] . '&amp;height=' . $instance['height'] . '" title="' . $image->alttext . '" alt="' . $image->alttext . '"/>';
        else
            $out .= '<img src="' . $image->thumbURL . '" width="' . $instance['width'] . '" height="' . $instance['height'] . '" title="' . $image->alttext . '" alt="' . $image->alttext . '"/>';

        echo $out . '</a>';
    }
}

echo '</div>';
echo $after_widget;
