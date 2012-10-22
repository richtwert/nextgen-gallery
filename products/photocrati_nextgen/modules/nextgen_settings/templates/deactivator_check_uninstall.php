<h2>Uninstall NextGEN?</h2>

<form method='POST'>

    <input type='submit'
           name='check_uninstall[deactivate]'
           value='<?php echo $deactivate_label; ?>'/>

    <input type='submit'
           name='check_uninstall[uninstall]'
           value='<?php echo $uninstall_label; ?>'
           onclick="javascript:return confirm('<?php echo $uninstall_confirm; ?>');"/>

</form>

<table>
    <tr>
        <td class='column1' colspan='2'>
            <p style='color: red;'>
                <strong><?php echo $uninstall_warning_2; ?></strong>
                <?php echo $uninstall_warning_3; ?>
            </p>
            <p>
                <?php echo $uninstall_tables_desc; ?>
            </p>
            <ul>
                <?php foreach ($uninstall_tables as $table) { ?>
                    <li>
                        <?php echo $table; ?>
                    </li>
                <?php } ?>
            </ul>
        </td>
    </tr>
</table>
