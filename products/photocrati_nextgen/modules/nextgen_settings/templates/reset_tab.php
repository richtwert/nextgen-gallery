<?php global $wpdb; ?>
<table>
    <tr>
        <td class='column1'>
            <?php echo $reset_label; ?>
        </td>
        <td>
            <input type="submit"
                   class="button-secondary"
                   name="resetdefault"
                   value="<?php echo $reset_value; ?>"
                   onclick="javascript:return confirm('<?php echo $reset_warning; ?>');"/>
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
        <tr>
            <td class='column1' colspan='2'>
                <?php var_dump($check_uninstall_url); ?>
                <p style='color: red;'>
                    <strong><?php echo $uninstall_warning_2; ?></strong>
                    <?php echo $uninstall_warning_3; ?>
                </p>
                <p>
                    <?php echo $uninstall_desc; ?>
                    <ul>
                        <?php foreach ($uninstall_tables as $table) { ?>
                        <li><?php echo $table; ?></li>
                        <?php } ?>
                    </ul>
                </p>
            </td>
        </tr>
    <?php } ?>
</table>
