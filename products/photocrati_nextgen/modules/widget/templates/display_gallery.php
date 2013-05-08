<?php
$controller  = C_Component_Registry::get_instance()->get_utility('I_Display_Type_Controller');
$effect_code = $controller->get_effect_code($gallery->displayed_gallery);
$settings    = $gallery->displayed_gallery->get_entity()->display_settings;

echo $settings['widget_setting_before_widget'] . $settings['widget_setting_before_title'] . $settings['widget_setting_title'] . $settings['widget_setting_after_title'];
echo '<div class="ngg-widget entry-content">';

foreach ($images as $image) {
    $out = '<a href="' . $image->imageURL . '" data-image-id="' . $image->pid . '" title="' . $image->description . '" ' . $effect_code . '>';

    if ($settings['widget_setting_show_setting'] == 'original')
        $out .= '<img src="' . trailingslashit(home_url()) . 'index.php?callback=image&amp;pid=' . $image->pid . '&amp;width=' . $settings['widget_setting_width'] . '&amp;height=' . $settings['widget_setting_height'] . '" title="' . $image->alttext . '" alt="' . $image->alttext . '"/>';
    else
        $out .= '<img src="' . $image->thumbURL . '" width="' . $settings['widget_setting_width'] . '" height="' . $settings['widget_setting_height'] . '" title="' . $image->alttext . '" alt="' . $image->alttext . '"/>';

    echo $out . '</a>';
}

echo '</div>';
echo $settings['widget_setting_after_widget'];
