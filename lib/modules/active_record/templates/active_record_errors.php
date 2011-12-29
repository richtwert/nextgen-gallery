<?php if ($record->has_errors()) { ?>
<div class='active_record_errors'>
    <h4>Validation errors:</h4>
    <p>Please correct the following</p>
    <ul>
    <?php foreach ($record->get_errors() as $property => $errors) { 
        foreach ($errors as $error) { ?>
        <li><?php echo_h($error) ?></li>
    <?php } }  ?>
    </ul>
</div>
<?php } ?>