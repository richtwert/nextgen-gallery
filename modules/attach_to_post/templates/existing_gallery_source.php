<table id="existing_gallery_fields" class="gallery_source_fields">
    <tr>
        <th>
            <label for='gallery_id'><?php echo_h(_("Gallery")) ?>:</label>
        </th>
        <td>
            <select name="gallery_id" id="gallery_id">
                <?php foreach ($galleries as $gallery): ?>
                <option <?php selected($gallery->id(), $selected_gallery_id)?> value="<?php echo_h($gallery->id()) ?>"><?php echo_h($gallery->name)?></option>
                <?php endforeach ?>
            </select>
        </td>
    </tr>
</table>