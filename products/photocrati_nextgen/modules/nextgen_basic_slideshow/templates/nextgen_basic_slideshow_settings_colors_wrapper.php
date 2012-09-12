<tr class="nextgen-settings-slideshow-flash <?php print ($hidden) ? 'hidden' : ''; ?>"
    id="nextgen-settings-slideshow-colors-wrapper">
    <td colspan='2'>
        <table>
            <tr>
                <?php $i = 0; ?>
                <?php foreach ($output as $td) { ?>
                    <td>
                        <?php print $td; ?>
                    </td>
                    <?php $i++; ?>
                    <?php if ($i == 2) { print "</tr><tr>"; $i = 0;} ?>
                <?php } ?>
            </tr>
        </table>
    </td>
</tr>
