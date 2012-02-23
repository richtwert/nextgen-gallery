<?php $hidden_fields = array() ?>

<table class="form_fields">
    <?php if (empty($fields)): ?>
    <p><?php echo_h(_("No options configurable for this gallery type."))?></p>
    <?php endif ?>
    <?php while (!empty($fields)): ?>
    <tr>
        <?php for($i=0; $i<2; $i++): ?>
            <?php if (($field = array_pop($fields))): ?>
                <?php if (isset($field['hidden'])): ?>
                    <?php $hidden_fields[] = $field; ?>
                    <?php $i-- ?>
                <?php else: ?>
                    <th class="column_<?php echo $i+1 ?>_label">
                        <label for="<?php echo_h($field['id'])?>">
                            <?php if (isset($field['help'])): ?>
                            <span class="tooltip" title="<?php echo_h($field['help'])?>">
                            <?php else: ?>
                            <span>
                            <?php endif; ?>
                            <?php echo_h($field['label']) ?>:
                            </span>
                        </label>
                    </th>
                    <td class="column_<?php echo $i+1 ?>_field">
                        <?php if (isset($field['help'])): ?>
                            <span class='tooltip' title="<?php echo_h($field['help'])?>">
                                <?php $this->render_field($field) ?>
                            </span>
                        <?php else: ?>
                            <?php $this->render_field($field) ?>
                        <?php endif ?>
                    </td>
                <?php endif ?>
            <?php else: ?>
                <td></td>
                <td></td>
            <?php endif ?>
        <?php endfor; ?>
    </tr>
    <?php endwhile; ?>
</table>
<?php foreach ($hidden_fields as $field): ?>
<?php $this->render_field($field) ?>
<?php endforeach; ?>
