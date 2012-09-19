<?php if (!empty($image)) { ?>
    <a href="<?php echo esc_attr($settings['link']); ?>"
       title="<?php echo esc_attr($image->description)?>"
       <?php echo $effect_code ?>>
        <img class="ngg-singlepic <?php echo $settings['float']; ?>"
             src="<?php echo $thumbnail_url; ?>"
             alt="<?php echo esc_attr($image->alttext); ?>"
             title="<?php echo esc_attr($image->alttext); ?>"
             <?php if (!empty($settings['width']))  { ?>width="<?php echo esc_attr($settings['width']); ?>"<?php } ?>
             <?php if (!empty($settings['height'])) { ?>height="<?php echo esc_attr($settings['height']); ?>"<?php } ?>/></a>
    <?php if (!is_null($inner_content)) { ?><span><?php echo $inner_content; ?></span><?php } ?>
<?php } ?>
