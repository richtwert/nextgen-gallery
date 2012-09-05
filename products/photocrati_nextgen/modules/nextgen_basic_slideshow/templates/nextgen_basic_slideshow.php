<?php

$anchor = 'ngg-slideshow-' . $displayed_gallery_id . '-' . $current_page;
$aspect_ratio = $gallery_width / $gallery_height;
?>
	
	<!-- Images -->
	<div
		class="ngg-slideshow-image-list ngg-slideshow-nojs"
		id="<?php echo_h($anchor)?>-image-list">
	<?php for ($i=0; $i<count($images); $i++): ?>
        <?php 
        
        $image = $images[$i];
        $image_size = $storage->get_original_dimensions($image);
        
        if ($image_size == null) {
        	$image_size['width'] = $image->meta_data['width'];
        	$image_size['height'] = $image->meta_data['height'];
        }

        if (isset($image->hidden) && $image->hidden) {
          $image->style = 'style="display: none;"';
        }
        else {
        	$image->style = '';
        }
        
        $image_ratio = $image_size['width'] / $image_size['height'];
        
        if ($image_ratio > $aspect_ratio) {
        	if ($image_size['width'] > $gallery_width) {
        		$image_size['width'] = $gallery_width;
        		$image_size['height'] = (int) round($gallery_width / $image_ratio);
        	}
        }
        else {
        	if ($image_size['height'] > $gallery_height) {
        		$image_size['width'] = (int) round($gallery_height * $image_ratio);
        		$image_size['height'] = $gallery_height;
        	}
        }
        
        ?>

        <div id="ngg-image-<?php echo_h($i)?>" class="ngg-gallery-slideshow-image" <?php print $image->style; ?>>
            <img
                title="<?php echo esc_attr($image->description)?>"
                alt="<?php echo esc_attr($image->alttext)?>"
                src="<?php echo esc_attr($storage->get_image_url($image))?>"
                width="<?php echo esc_attr($image_size['width'])?>"
                height="<?php echo esc_attr($image_size['height'])?>"
            />
        </div>

	<?php endfor ?>
	</div>
	
<div
	class="ngg-galleryoverview ngg-slideshow"
	id="<?php echo_h($anchor)?>"
	style="width:<?php echo_h($gallery_width) ?>px; height:<?php echo_h($gallery_height) ?>px;">

	<div
		class="ngg-slideshow-loader"
		id="<?php echo_h($anchor)?>-loader"
		style="width:<?php echo_h($gallery_width) ?>px; height:<?php echo_h($gallery_height) ?>px;">
		<img src="<?php echo_h(NGGALLERY_URLPATH) ?>images/loader.gif" alt="" />
	</div>

	<?php if ($pagination): ?>
	<!-- Pagination -->
	<?php echo $pagination ?>
	<?php else: ?>
	<div class="ngg-clear"></div>
	<?php endif ?>
</div>
<script type="text/javascript">
jQuery('#<?php echo_h($anchor)?>-image-list').hide().removeClass('ngg-slideshow-nojs');
jQuery(document).ready(function(){ 
	jQuery('#<?php echo_h($anchor)?>').nggShowSlideshow({
		id: '<?php echo_h($displayed_gallery_id)?>',
		fx: '<?php echo_h($cycle_effect)?>',
		width: <?php echo_h($gallery_width)?>,
		height: <?php echo_h($gallery_height)?>,
		domain: '<?php echo_h(trailingslashit ( home_url() ))?>',
		timeout: <?php echo_h($cycle_interval)?>
	});
});
</script>
