<div
	class="ngg-galleryoverview"
	id="ngg-gallery-<?php echo_h($displayed_gallery_id)?>-<?php echo_h($current_page)?>">

    <?php if (!empty($slideshow_link)): ?>
	<div class="slideshowlink">
        <a href='<?php echo $slideshow_link ?>'><?php echo $slideshow_link_text ?></a>
		
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
        <?php $thumb_size = $storage->get_image_dimensions($image, $thumbnail_size_name); ?>

        <?php if (isset($image->hidden) && $image->hidden): ?>
            <?php $image->style = 'style="display: none;"'?>
        <?php else: ?>
            <?php $image->style = ''; ?>
        <?php endif; ?>

        <div id="ngg-image-<?php echo_h($i)?>" class="ngg-gallery-thumbnail-box" <?php print $image->style; ?>>
            <div class="ngg-gallery-thumbnail">
                <a href="<?php echo esc_attr($storage->get_image_url($image))?>"
                   title="<?php echo esc_attr($image->description)?>"
                   data-image-id='<?php echo esc_attr($image->pid); ?>'
                   <?php echo $effect_code ?>>
                    <img
                        title="<?php echo esc_attr($image->alttext)?>"
                        alt="<?php echo esc_attr($image->alttext)?>"
                        src="<?php echo esc_attr($storage->get_image_url($image, $thumbnail_size_name))?>"
                        width="<?php echo esc_attr($thumb_size['width'])?>"
                        height="<?php echo esc_attr($thumb_size['height'])?>"
                        style="max-width:none;"
                    />
                </a>
                <?php
                /*
                    $triggers = $this->get_registry()->get_utility('I_NextGen_Pro_Lightbox_Trigger_Manager');

                    if ($triggers != null && defined('NEXTGEN_PRO_LIGHTBOX_MODULE_NAME') && $this->object->get_registry()->get_utility('I_NextGen_Settings')->thumbEffect == NEXTGEN_PRO_LIGHTBOX_MODULE_NAME)
                    {
                        $params = array(
                            'context' => 'image',
                            'context-id' => $image->{$image->id_field},
                            'context-parent' => 'gallery',
                            'context-parent-id' => $transient_id,
                        );

                        echo $triggers->render_trigger_list(null, $params, $this->object);
                    }
                */
                ?>
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
