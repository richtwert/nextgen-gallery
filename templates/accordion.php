<div class='accordion'>
<?php foreach ($tabs as $heading => $content) { ?>
    <h3>
        <a href='#<?php echo_h(str_replace(' ', '_', $heading)) ?>'>
            <?php echo_h($heading) ?>
        </a>
    </h3>
    <div class="accordion_tab">
        <?php echo $content ?>
    </div>
<?php } ?>
</div>
