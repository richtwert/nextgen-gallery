<?php
$controller = C_Component_Registry::get_instance()->get_utility('I_Display_Type_Controller');
$storage    = C_Component_Registry::get_instance()->get_utility('I_Gallery_Storage');

$effect_code = $controller->get_effect_code($gallery->displayed_gallery);
$settings    = $gallery->displayed_gallery->get_entity()->display_settings;

echo $settings['widget_setting_before_widget']
     . $settings['widget_setting_before_title']
     . $settings['widget_setting_title']
     . $settings['widget_setting_after_title'];
?>
<?php // keep the following a/img on the same line ?>
<div class="ngg-widget entry-content">
    <?php
    foreach ($images as $image) {
        $thumb_size = $storage->get_image_dimensions($image, $settings['thumbnail_size_name']);
        ?>
        <a href="<?php echo esc_attr($storage->get_image_url($image))?>"
           title="<?php echo esc_attr($image->description)?>"
           data-image-id='<?php echo esc_attr($image->pid); ?>'
           <?php echo $effect_code ?>
           ><img title="<?php echo esc_attr($image->alttext)?>"
                 alt="<?php echo esc_attr($image->alttext)?>"
                 src="<?php echo esc_attr($storage->get_image_url($image, $settings['thumbnail_size_name']))?>"
                 width="<?php echo esc_attr($thumb_size['width'])?>"
                 height="<?php echo esc_attr($thumb_size['height'])?>"
            /></a>
    <?php } ?>
</div>

<?php echo $settings['widget_setting_after_widget']; ?>