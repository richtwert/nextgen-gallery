<tr>
    <td>
        <label for='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'>
            <?php echo_h($show_slideshow_link_label); ?>
        </label>
    </td>
    <td>
		<?php var_dump($show_alternative_view_link) ?>
        <input type='checkbox'
               id='<?php echo esc_attr($display_type_name); ?>_show_slideshow_link'
               name='<?php echo esc_attr($display_type_name); ?>[show_alternative_view_link]'
               class='ngg_thumbnail_show_slideshow_link'
               value='photocrati-nextgen_basic_slideshow'
                <?php echo checked('photocrati-nextgen_basic_slideshow', $show_alternative_view_link); ?>'>
        </select>
    </td>
</tr>
