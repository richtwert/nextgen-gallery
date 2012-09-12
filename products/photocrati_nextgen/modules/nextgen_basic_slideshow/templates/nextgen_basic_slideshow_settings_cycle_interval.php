<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_cycle_interval'>
            <?php echo_h($cycle_interval_label); ?>
        </label>
    </td>
    <td>
        <input type='number'
               id='<?php echo esc_attr($display_type_name); ?>_cycle_interval'
               name='<?php echo esc_attr($display_type_name); ?>[cycle_interval]'
               class='ngg_slideshow_cycle_interval'
               value='<?php echo esc_attr($cycle_interval); ?>'
               required='required'
               min='1'
               placeholder='<?php _e('# of seconds'); ?>'/>
    </td>
</tr>
