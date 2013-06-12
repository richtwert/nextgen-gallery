<?php if (!empty($image)): ?>
    <?php

    $template_params = array(
            'index' => 0,
            'class' => 'ngg-gallery-singlepic-image',
            'image' => $image,
        );

    $this->include_template('nextgen_gallery_display#image/before', $template_params);

    ?>
    <a href="<?php echo esc_attr($settings['link']); ?>"
       title="<?php echo esc_attr($image->description)?>"
       data-image-id='<?php echo esc_attr($image->pid); ?>'
       <?php echo $effect_code ?>>
        <img class="ngg-singlepic <?php echo $settings['float']; ?>"
             src="<?php echo $thumbnail_url; ?>"
             alt="<?php echo esc_attr($image->alttext); ?>"
             title="<?php echo esc_attr($image->alttext); ?>"
             <?php if (!empty($settings['width']))  { ?>width="<?php echo esc_attr($settings['width']); ?>"<?php } ?>
             <?php if (!empty($settings['height'])) { ?>height="<?php echo esc_attr($settings['height']); ?>"<?php } ?>/></a>
    <?php if (!is_null($inner_content)) { ?><span><?php echo $inner_content; ?></span><?php } ?>
    <?php $this->include_template('nextgen_gallery_display#image/after', $template_params); ?>
<?php else: ?>
    <p>No image found</p>
<?php endif ?>
