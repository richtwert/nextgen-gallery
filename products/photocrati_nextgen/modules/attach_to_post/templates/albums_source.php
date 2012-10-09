<script type="text/x-handlebars" data-template-name="<?php echo esc_attr($template_name)?>">
    <tr>
        <td>
            <label for="albums">
                <?php echo_h($existing_albums_label)?>
            </label>
        </td>
        <td>
            {{view Ember.Chosen
            viewName="select"
            contentBinding="NggDisplayTab.albums"
            selectionBinding="NggDisplayTab.displayed_gallery.containers"
            optionLabelPath="content.name"
            optionValuePath="content.id"
            multiple="multiple"
            class="pretty-dropdown"
            id="albums"
            fillCallback="fetch_albums"
            }}
        </td>
    </tr>
</script>