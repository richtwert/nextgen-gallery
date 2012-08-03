<table id="existing_gallery_fields" class="gallery_source_fields">
    <tr>
        <th>
            <label for='gallery_id'><?php echo_h(_("Gallery")) ?>:</label>
        </th>
        <td>
            <select name="gallery_id" id="gallery_id">
                <?php foreach ($galleries as $gallery): ?>
				<?php print_r($gallery); ?>
                <option <?php selected($gallery->$gallery_key, $selected_gallery_id)?> value="<?php echo_h($gallery->$gallery_key) ?>"><?php echo_h($gallery->name)?></option>
                <?php endforeach ?>
            </select>
        </td>
    </tr>
</table>