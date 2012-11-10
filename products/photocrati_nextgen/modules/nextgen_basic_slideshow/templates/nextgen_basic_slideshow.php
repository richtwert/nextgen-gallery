<?php if ($return_link): ?>
<!-- Thumbnails Link -->
<div class="slideshowlink">
	<?php echo $return_link ?>
</div>
<?php elseif ($alternative_view_link): ?>
<div class="slideshowlink">
	<?php echo $alternative_view_link ?>
</div>
<?php endif ?>

<?php if ($flash_enabled): ?>
	<!-- Display Flash Slideshow -->

	<?php
	// Configure slideshow parameters
	$width = $gallery_width;
	$height = $gallery_height;

		if ($cycle_interval == 0)
			$cycle_interval = 1;

    // init the flash output
    $swfobject = new swfobject( $flash_path, 'so' . $displayed_gallery_id, $width, $height, '7.0.0', 'false');

    $swfobject->message = '<p>'. __('The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed.', 'nggallery').'</p>';
    $swfobject->add_params('wmode', 'opaque');
    $swfobject->add_params('allowfullscreen', 'true');
    $swfobject->add_params('bgcolor', $flash_screen_color, 'FFFFFF', 'string', '#');
    $swfobject->add_attributes('styleclass', 'slideshow');
    $swfobject->add_attributes('name', 'so' . $displayed_gallery_id);

    // adding the flash parameter
    //$swfobject->add_flashvars( 'file', urlencode ( trailingslashit ( home_url() ) . 'index.php?callback=imagerotator&gid=' . $displayed_gallery_id ) );
    $swfobject->add_flashvars( 'file', urlencode ( $mediarss_link ) );
    $swfobject->add_flashvars( 'shuffle', $flash_shuffle, 'true', 'bool');
    // option has oposite meaning : true should switch to next image
    $swfobject->add_flashvars( 'linkfromdisplay', !$flash_next_on_click, 'false', 'bool');
    $swfobject->add_flashvars( 'shownavigation', $flash_navigation_bar, 'true', 'bool');
    $swfobject->add_flashvars( 'showicons', $flash_loading_icon, 'true', 'bool');
    $swfobject->add_flashvars( 'kenburns', $flash_slow_zoom, 'false', 'bool');
    $swfobject->add_flashvars( 'overstretch', $flash_stretch_image, 'false', 'string');
    $swfobject->add_flashvars( 'rotatetime', $cycle_interval, 5, 'int');
    $swfobject->add_flashvars( 'transition', $flash_transition_effect, 'random', 'string');
    $swfobject->add_flashvars( 'backcolor', $flash_background_color, 'FFFFFF', 'string', '0x');
    $swfobject->add_flashvars( 'frontcolor', $flash_text_color, '000000', 'string', '0x');
    $swfobject->add_flashvars( 'lightcolor', $flash_rollover_color, '000000', 'string', '0x');
    $swfobject->add_flashvars( 'screencolor', $flash_screen_color, '000000', 'string', '0x');
    if ($flash_watermark_logo) {
		$ngg_options = $this->object->get_registry()->get_utility('I_NextGen_Settings');
		$swfobject->add_flashvars( 'logo', $ngg_options['wmPath'], '', 'string');
	}


    $swfobject->add_flashvars( 'audio', $flash_background_music, '', 'string');
    $swfobject->add_flashvars( 'width', $width, '260');
    $swfobject->add_flashvars( 'height', $height, '320');
    // create the output
    $out  = '<div class="slideshow">' . $swfobject->output() . '</div>';
    // add now the script code
    $out .= "\n".'<script type="text/javascript" defer="defer">';
    // load script via jQuery afterwards
    // $out .= "\n".'jQuery.getScript( "' . esc_js( includes_url('js/swfobject.js') ) . '", function() {} );';
    if ($flash_xhtml_validation) $out .= "\n".'<!--';
    if ($flash_xhtml_validation) $out .= "\n".'//<![CDATA[';
    $out .= $swfobject->javascript();
    if ($flash_xhtml_validation) $out .= "\n".'//]]>';
    if ($flash_xhtml_validation) $out .= "\n".'-->';
    $out .= "\n".'</script>';
	echo apply_filters('ngg_show_slideshow_content', $out, $displayed_gallery_id, $width, $height);
	?>

<?php else: ?>
	<!-- Display JQuery Cycle Slideshow -->
	<div class="ngg-slideshow-image-list ngg-slideshow-nojs" id="<?php echo_h($anchor)?>-image-list">
		<?php for ($i=0; $i<count($images); $i++): ?>

			<?php
			// Determine image dimensions
			$image = $images[$i];
			$image_size = $storage->get_original_dimensions($image);

			if ($image_size == null) {
				$image_size['width'] = $image->meta_data['width'];
				$image_size['height'] = $image->meta_data['height'];
			}

			// Determine whether an image is hidden or not
			if (isset($image->hidden) && $image->hidden) {
			  $image->style = 'style="display: none;"';
			}
			else {
				$image->style = '';
			}

			// Determine image aspect ratio
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
					style="max-width:none;"
				/>
			</div>
		<?php endfor ?>
	</div>

	<div
		class="ngg-galleryoverview ngg-slideshow"
		id="<?php echo_h($anchor)?>"
		style="width:<?php echo_h($gallery_width) ?>px; height:<?php echo_h($gallery_height) ?>px;overflow: visible;">

		<div
			class="ngg-slideshow-loader"
			id="<?php echo_h($anchor)?>-loader"
			style="width:<?php echo_h($gallery_width) ?>px; height:<?php echo_h($gallery_height) ?>px;">
			<img src="<?php echo_h(NGGALLERY_URLPATH) ?>images/loader.gif" alt="" />
		</div>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	jQuery('#<?php echo_h($anchor)?>-image-list').hide().removeClass('ngg-slideshow-nojs');
	
	jQuery(document).ready(function(){
		jQuery('#<?php echo_h($anchor)?>').nggShowSlideshow({
			id: '<?php echo_h($displayed_gallery_id)?>',
			fx: '<?php echo_h($cycle_effect)?>',
			width: <?php echo_h($gallery_width)?>,
			height: <?php echo_h($gallery_height)?>,
			domain: '<?php echo_h(trailingslashit(home_url()))?>',
			timeout: <?php echo_h(intval($cycle_interval)*1000)?>
		});
	});
	//]]>
	</script>
<?php endif ?>
