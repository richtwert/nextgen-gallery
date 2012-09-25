<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_thumbnails_link' class="tooltip">
            <?php echo_h($show_thumbnails_link_label); ?>
			<span>
				<?php echo_h($tooltip) ?>
			</span>
        </label>
    </td>
    <td>
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_thumbnails_link'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='ngg_thumbnail_show_thumbnails_link'
			value='<?php echo esc_attr(PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS)?>'
			<?php echo checked(PHOTOCRATI_GALLERY_NEXTGEN_BASIC_THUMBNAILS, $show_alternative_view_link); ?>/>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_thumbnails_link'>Yes</label>
		&nbsp;
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_thumbnails_link_no'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='ngg_thumbnail_show_thumbnails_link'
			value='0'
			<?php echo checked(0, $show_alternative_view_link); ?>/>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_thumbnails_link_no'>No</label>
    </td>
</tr>
