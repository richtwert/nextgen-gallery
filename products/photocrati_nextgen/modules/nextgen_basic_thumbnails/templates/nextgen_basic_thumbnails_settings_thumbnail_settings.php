<?php
if ($override_thumbnail_settings == null) {
	$override_thumbnail_settings = 0;
}

if ($thumbnail_watermark == null) {
	$thumbnail_watermark = 0;
}

if ($thumbnail_crop == null) {
	$thumbnail_crop = 0;
}

$extra_settings_class = $override_thumbnail_settings ? '' : 'hidden';

if ($extra_settings_class != null) {
	$extra_settings_class = ' ' . $extra_settings_class;
}
?>
<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_override_thumbnail_settings'>
            <?php echo_h($override_thumbnail_settings_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_override_thumbnail_settings'
               name='<?php echo esc_attr($display_type_name); ?>[override_thumbnail_settings]'
               class='ngg_thumbnail_override_thumbnail_settings'
               value='1'
               <?php echo checked(1, intval($override_thumbnail_settings)); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_override_thumbnail_settings'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_override_thumbnail_settings_no'
               name='<?php echo esc_attr($display_type_name); ?>[override_thumbnail_settings]'
               class='ngg_thumbnail_override_thumbnail_settings'
               value='0'
               <?php echo checked(0, $override_thumbnail_settings); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_override_thumbnail_settings_no'><?php _e('No'); ?></label>
    </td>
</tr>
<tr class="nextgen-basic-thumbnails-thumbnail-settings<?php echo esc_attr($extra_settings_class); ?>">
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_dimensions'>
            <?php echo_h($thumbnail_dimensions_label); ?>
        </label>
    </td>
    <td>
		<?php
		  $thumbnails_template_width_value = $thumbnail_width;
		  $thumbnails_template_height_value = $thumbnail_height;
		  $thumbnails_template_id = $display_type_name . '_thumbnail_dimensions';
		  $thumbnails_template_width_id = $display_type_name . '_thumbnail_width';
		  $thumbnails_template_height_id = $display_type_name . '_thumbnail_height';
		  $thumbnails_template_name = $display_type_name . '_thumbnail_dimensions';
		  $thumbnails_template_width_name = $display_type_name . '[thumbnail_width]';
		  $thumbnails_template_height_name = $display_type_name . '[thumbnail_height]';
		  include(path_join(NGGALLERY_ABSPATH, implode(DIRECTORY_SEPARATOR, array('admin', 'thumbnails-template.php'))));
		?>
    </td>
</tr>
<tr class="nextgen-basic-thumbnails-thumbnail-settings<?php echo esc_attr($extra_settings_class); ?>">
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_quality'>
            <?php echo_h($thumbnail_quality_label); ?>
        </label>
    </td>
    <td>
			<select name='<?php echo esc_attr($display_type_name); ?>[thumbnail_quality]' id='<?php echo esc_attr($display_type_name); ?>_thumbnail_quality'>
			<?php for($i=100; $i>50; $i--): ?>
				<option
					<?php selected($i, $thumbnail_quality) ?>
					value="<?php echo_h($i)?>"><?php echo_h($i) ?>%</option>
			<?php endfor ?>
			</select>
    </td>
</tr>
<tr class="nextgen-basic-thumbnails-thumbnail-settings<?php echo esc_attr($extra_settings_class); ?>">
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_crop'>
            <?php echo_h($thumbnail_crop_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_thumbnail_crop'
               name='<?php echo esc_attr($display_type_name); ?>[thumbnail_crop]'
               class='ngg_thumbnail_thumbnail_crop'
               value='1'
               <?php echo checked(1, intval($thumbnail_crop)); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_crop'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_thumbnail_crop_no'
               name='<?php echo esc_attr($display_type_name); ?>[thumbnail_crop]'
               class='ngg_thumbnail_thumbnail_crop'
               value='0'
               <?php echo checked(0, $thumbnail_crop); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_crop_no'><?php _e('No'); ?></label>
    </td>
</tr>
<tr class="nextgen-basic-thumbnails-thumbnail-settings<?php echo esc_attr($extra_settings_class); ?>">
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_watermark'>
            <?php echo_h($thumbnail_watermark_label); ?>
        </label>
    </td>
    <td>
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_thumbnail_watermark'
               name='<?php echo esc_attr($display_type_name); ?>[thumbnail_watermark]'
               class='ngg_thumbnail_thumbnail_watermark'
               value='1'
               <?php echo checked(1, intval($thumbnail_watermark)); ?>'>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_watermark'><?php _e('Yes'); ?></label>
        &nbsp;
        <input type='radio'
               id='<?php echo esc_attr($display_type_name); ?>_thumbnail_watermark_no'
               name='<?php echo esc_attr($display_type_name); ?>[thumbnail_watermark]'
               class='ngg_thumbnail_thumbnail_watermark'
               value='0'
               <?php echo checked(0, $thumbnail_watermark); ?>/>
        <label for='<?php echo esc_attr($display_type_name); ?>_thumbnail_watermark_no'><?php _e('No'); ?></label>
    </td>
</tr>
