<?php global $wpdb; ?>
<table>
    <tr>
        <td class='column1'>
            <span class='tooltip'>
                <?php echo $reset_label; ?>
                <span><?php echo $reset_warning; ?></span>
            </span>
        </td>
        <td>
            <input type="submit"
                   class="button-secondary"
                   name="resetdefault"
                   value="<?php echo $reset_value; ?>"
                   onclick="javascript:return confirm('<?php echo $reset_confirmation; ?>');"/>
        </td>
    </tr>
    <?php if ($show_uninstall) { ?>
        <tr>
            <td class='column1'>
                <?php echo $uninstall_label; ?>
            </td>
            <td>
                <button type='button'
                       name="check_uninstall"
                       class="button delete button-secondary"
                       onclick='location.href="<?php echo $check_uninstall_url; ?>";'/>
                    <?php echo $uninstall_label; ?>
                </button>
            </td>
        </tr>
    <?php } ?>
</table>
