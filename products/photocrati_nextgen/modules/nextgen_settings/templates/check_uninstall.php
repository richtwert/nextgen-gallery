<h2>Uninstall NextGEN?</h2>

<p><?php echo $uninstall_warning; ?></p>

<form method='POST'>

    <input type='submit'
           name='check_uninstall[deactivate]'
           value='<?php echo $deactivate_label; ?>'/>

    <input type='submit'
           name='check_uninstall[uninstall]'
           value='<?php echo $uninstall_label; ?>'
           onclick="javascript:return confirm('<?php echo $uninstall_warning; ?>');"/>

</form>
