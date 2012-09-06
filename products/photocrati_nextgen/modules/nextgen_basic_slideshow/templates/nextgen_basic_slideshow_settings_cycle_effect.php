<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_cycle_effect'>
            <?php echo_h($cycle_effect_label); ?>
        </label>
    </td>
    <td>
        <select
               id='<?php echo esc_attr($display_type_name); ?>_cycle_effect'
               name='<?php echo esc_attr($display_type_name); ?>[cycle_effect]'
               class='ngg_slideshow_cycle_effect'
               value='<?php echo esc_attr($cycle_effect); ?>'>
						<option value="fade" <?php selected('fade', $cycle_effect); ?> ><?php _e('fade', 'nggallery') ;?></option>
						<option value="blindX" <?php selected('blindX', $cycle_effect); ?> ><?php _e('blindX', 'nggallery') ;?></option>
						<option value="cover" <?php selected('cover', $cycle_effect); ?> ><?php _e('cover', 'nggallery') ;?></option>
						<option value="scrollUp" <?php selected('scrollUp', $cycle_effect); ?> ><?php _e('scrollUp', 'nggallery') ;?></option>
						<option value="scrollDown" <?php selected('scrollDown', $cycle_effect); ?> ><?php _e('scrollDown', 'nggallery') ;?></option>
						<option value="shuffle" <?php selected('shuffle', $cycle_effect); ?> ><?php _e('shuffle', 'nggallery') ;?></option>
						<option value="toss" <?php selected('toss', $cycle_effect); ?> ><?php _e('toss', 'nggallery') ;?></option>
						<option value="wipe" <?php selected('wipe', $cycle_effect); ?> ><?php _e('wipe', 'nggallery') ;?></option>
        </select>
    </td>
</tr>
