<tr>
    <th>
        <label for='caption_<?php echo_h($order)?>'><?php echo_h(_("Caption"))?>:</label>
    </th>
    <td>
        <input
            type="text"
            name="images[<?php echo_h($order)?>][alttext]"
            id="caption_<?php echo_h($order)?>"
            value="<?php echo_h($image->alttext)?>"
        />
    </td>
</tr>