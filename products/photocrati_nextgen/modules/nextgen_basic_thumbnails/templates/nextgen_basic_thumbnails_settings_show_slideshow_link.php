<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'>
            <?php echo_h($show_slideshow_link_label); ?>
        </label>
    </td>
    <td>
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='ngg_thumbnail_show_slideshow_link'
			value='photocrati-nextgen_basic_slideshow'
			<?php echo checked('photocrati-nextgen_basic_slideshow', $show_alternative_view_link); ?>'>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'>Yes</label>
		&nbsp;
		<input type="radio"
			id='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link_no'
			name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
			class='ngg_thumbnail_show_slideshow_link'
			value=''
			<?php echo checked('', $show_alternative_view_link); ?>'>
		<label for='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link_no'>No</label>
    </td>
</tr>
