<div
	class="ngg-galleryoverview"
	id="ngg-gallery-<?php echo_h($displayed_gallery_id)?>-<?php echo_h($current_page)?>">

	<?php if ($show_alternative_view_link): ?>
	<!-- Slideshow Link -->
	<div class="slideshowlink">
		<?php echo $alternative_view_link ?>
	</div>
	<?php endif ?>

	<?php if (!empty($return_link)): ?>
	<!-- Return link -->
	<div class="slideshowlink">
		<?php echo $return_link ?>
	</div>
	<?php endif ?>

	<?php if ($show_piclens_link): ?>
	<!-- Piclense link -->
	<div class="piclenselink">
		<a class="piclenselink" href="<?php echo esc_attr($piclens_link) ?>">
			<?php echo_h($piclens_link_text); ?>
		</a>
	</div>
	<?php endif ?>

	<!-- Thumbnails -->
	<?php for ($i=0; $i<count($images); $i++): ?>
        <?php $image = $images[$i]; ?>
        <?php $thumb_size = $storage->get_thumb_dimensions($image); ?>

        <?php if (isset($image->hidden) && $image->hidden): ?>
            <?php $image->style = 'style="display: none;"'?>
        <?php else: ?>
            <?php $image->style = ''; ?>
        <?php endif; ?>

        <div id="ngg-image-<?php echo_h($i)?>" class="ngg-gallery-thumbnail-box" <?php print $image->style; ?>>
            <div class="ngg-gallery-thumbnail">
                <a
                    href="<?php echo esc_attr($storage->get_image_url($image))?>"
                    title="<?php echo esc_attr($image->description)?>"
                    <?php echo $effect_code ?>>
                    <img
                        title="<?php echo esc_attr($image->alttext)?>"
                        alt="<?php echo esc_attr($image->alttext)?>"
                        src="<?php echo esc_attr($storage->get_thumb_url($image))?>"
                        width="<?php echo esc_attr($thumb_size['width'])?>"
                        height="<?php echo esc_attr($thumb_size['height'])?>"
                    />

                </a>
            </div>
        </div>

        <?php if ($number_of_columns > 0): ?>
            <?php if ((($i + 1) % $number_of_columns) == 0 ): ?>
                <br style="clear: both" />
            <?php endif; ?>
        <?php endif; ?>

	<?php endfor ?>

	<?php if ($pagination): ?>
	<!-- Pagination -->
	<?php echo $pagination ?>
	<?php else: ?>
	<div class="ngg-clear"></div>
	<?php endif ?>
</div>
<?php
?>
