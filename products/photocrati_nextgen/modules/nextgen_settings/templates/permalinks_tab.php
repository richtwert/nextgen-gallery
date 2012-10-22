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
    <tr class='nextgen-settings-permalinks-activated <?php print ($hidden) ? 'hidden' : ''; ?>'>
        <td class='column1'>
            <label for='permalinks_slug' class='tooltip'>
                <?php echo_h($permalinks_slug_label); ?>
                <span>
                    <?php echo_h($permalinks_slug_tooltip); ?>
                </span>
            </label>
        </td>
        <td>
            <input id='permalinks_slug'
                   type='text'
                   name='settings[permalinkSlug]'
                   value='<?php echo esc_attr($permalinks_slug); ?>'/>
        </td>
    </tr>
    <tr class='nextgen-settings-permalinks-activated <?php print ($hidden) ? 'hidden' : ''; ?>'>
        <td class='column1'>
            <span class='tooltip'>
                <?php echo_h($process_label); ?>
                <span><?php echo_h($process_tooltip); ?></span>
            </span>
        </td>
        <td>
            <input type='submit'
                   name='createslugs'
                   class='button-secondary'
                   value='<?php echo $process_value; ?> &raquo;'/>
        </td>
    </tr>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("input[name='settings[usePermalinks]']").click(function() {
                $("tr.nextgen-settings-permalinks-activated").toggle('slow');
            });
        });
    </script>
</table>
