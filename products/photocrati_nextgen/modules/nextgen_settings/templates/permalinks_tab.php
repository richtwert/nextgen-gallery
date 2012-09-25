<table>
    <tr>
        <td class='column1'>
            <label for='permalinks_activated'><?php echo_h($permalinks_activated_label); ?></label>
        </td>
        <td>
            <label for='permalinks_activated'><?php echo_h($permalinks_activated_yes); ?></label>
            <input id='permalinks_activated'
                   type='radio'
                   name='settings[usePermalinks]'
                   value='1'
                   <?php checked(TRUE, $permalinks_activated ? TRUE : FALSE)?>/>
            &nbsp;
            <label for='permalinks_activated_no'><?php echo_h($permalinks_activated_no); ?></label>
            <input id='permalinks_activated_no'
                   type='radio'
                   name='settings[usePermalinks]'
                   value='0'
                   <?php checked(FALSE, $permalinks_activated ? TRUE : FALSE); ?>/>
            <p class='description'>
                <?php echo_h($permalinks_activated_help); ?>
            </p>
        </td>
    </tr>
    <tr>
        <td class='column1'>
            <label for='permalinks_slug'>
                <?php echo_h($permalinks_slug_label); ?>
            </label>
        </td>
        <td>
            <input id='permalinks_slug'
                   type='text'
                   name='settings[permalinkSlug]'
                   value='<?php echo esc_attr($permalinks_slug); ?>'/>
        </td>
    </tr>
    <tr>
        <td class='column1'>
            <?php echo_h($process_label); ?>
        </td>
        <td>
            <input type='submit'
                   name='createslugs'
                   class='button-secondary'
                   value='<?php echo $process_value; ?> &raquo;'/>
        </td>
    </tr>
</table>
