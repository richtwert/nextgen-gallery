<?php if (!empty($image)): ?>
    <?php

    $template_params = array(
            'index' => 0,
            'class' => 'ngg-gallery-singlepic-image',
            'image' => $image,
        );

    $this->include_template('nextgen_gallery_display#image/before', $template_params);
    
		$image_size = $storage->get_original_dimensions($image);

		if ($image_size == null) {
			$image_size['width'] = $image->meta_data['width'];
			$image_size['height'] = $image->meta_data['height'];
		}
		
		$image_ratio = $image_size['width'] / $image_size['height'];
    
    $width = isset($settings['width']) ? $settings['width'] : null;
    $height = isset($settings['height']) ? $settings['height'] : null;
		
		if ($width != null && $height != null)
		{
			// check image aspect ratio, avoid distortions
			$aspect_ratio = $width / $height;
			if ($image_ratio > $aspect_ratio) {
				if ($image_size['width'] > $width) {
					$height = (int) round($width / $image_ratio);
				}
			}
			else {
				if ($image_size['height'] > $height) {
					$width = (int) round($height * $image_ratio);
				}
			}
			
			// Ensure that height is always null, or else the image won't be responsive correctly
			$height = null;
		}
		else if ($height != null)
		{
			$width = (int) round($height * $image_ratio);
			// Ensure that height is always null, or else the image won't be responsive correctly
			$height = null;
		}

    ?>
    <a href="<?php echo esc_attr($settings['link']); ?>"
       title="<?php echo esc_attr($image->description)?>"
       data-image-id='<?php echo esc_attr($image->pid); ?>'
       <?php echo $effect_code ?>>
        <img class="ngg-singlepic <?php echo $settings['float']; ?>"
             src="<?php echo $thumbnail_url; ?>"
             alt="<?php echo esc_attr($image->alttext); ?>"
             title="<?php echo esc_attr($image->alttext); ?>"
             <?php if ($width) { ?> width="<?php echo esc_attr($width); ?>" <?php } ?>
             <?php if ($height) { ?> height="<?php echo esc_attr($height); ?>" <?php } ?>/></a>
    <?php if (!is_null($inner_content)) { ?><span><?php echo $inner_content; ?></span><?php } ?>
    <?php $this->include_template('nextgen_gallery_display#image/after', $template_params); ?>
<?php else: ?>
    <p>No image found</p>
<?php endif ?>
