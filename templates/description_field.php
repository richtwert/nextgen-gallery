<tr>
    <th>
        <label for='description_<?php echo_h($order)?>'><?php echo_h(_("Description"))?>:</label>
    </th>
    <td>
        <textarea
            name="images[<?php echo_h($order)?>][description]"
            id="description_<?php echo_h($order)?>"
        ><?php echo_h($image->description)?></textarea>
    </td>
</tr>