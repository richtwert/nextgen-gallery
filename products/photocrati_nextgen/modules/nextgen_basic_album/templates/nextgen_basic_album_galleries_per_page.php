<tr>
    <td>
        <label class="tooltip" for="<?php echo esc_attr($display_type_name)?>_galleries_per_page">
            <?php echo_h($galleries_per_page_label) ?>
            <span><?php echo_h($galleries_per_page_help)?></span>
        </label>
    </td>
    <td>
        <input
            id="<?php echo esc_attr($display_type_name)?>_galleries_per_page"
            name="<?php echo esc_attr($display_type_name) ?>[galleries_per_page]"
            type="number"
            min="0"
            value="<?php echo esc_attr($galleries_per_page)?>"
            placeholder="#"
        />
    </td>
</tr>