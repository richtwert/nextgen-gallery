<table>
    <tr>
        <th>Custom Display Template:</th>
        <td>
            <input type="text" id="display_template" name="display_template"/>
            <p class="field_help">
                <em>
                    Provide filename of a custom template used to display the
                    gallery. Please provide an absolute path of the filename.
                    Otherwise, the filename is expected to be found in one of the
                    following directories:
                </em>
                <ul>
                <?php foreach($template_dirs as $dir) { ?>
                    <li><?php echo_h($dir) ?></li>
                <?php } ?>
                </ul>
            </p>
        </td>
    </tr>
</table>