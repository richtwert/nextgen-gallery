<?php
$anchor = 'ngg-slideshow-' . $displayed_gallery_id . '-' . $current_page;
?>
<div
	class="ngg-galleryoverview"
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
<script type="text/javascript" defer="defer">
jQuery(document).ready(function(){ 
	jQuery('#<?php echo_h($anchor)?>').nggSlideshow({
		id: '<?php echo_h($displayed_gallery_id)?>',
		fx: '<?php echo_h($cycle_effect)?>',
		width: <?php echo_h($gallery_width)?>,
		height: <?php echo_h($gallery_height)?>,
		domain: '<?php echo_h(trailingslashit ( home_url() ))?>',
		timeout: <?php echo_h($cycle_interval)?>
	});
});
</script>
