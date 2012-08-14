<html>
    <head>
        <title>Attach to Post</title>
        <?php $this->_render_scripts_and_styles() ?>
    </head>

    <body>
        <div id="errors" class="errors"></div>
        <form method='POST' id="attach_gallery" name="attach_gallery" action="<?php echo $_SERVER['PHP_SELF'] ?>">

            <input
                type="hidden"
                name="ID"
                id="attached_gallery_id"
                value="<?php echo_h($ID) ?>"
            />

            <input
                type="hidden"
                name="post_id"
                id="post_id"
                value="<?php echo_h($post_id) ?>"
            />

            <?php echo $accordion ?>

            <div class='buttons'>
                <input
                    name="save"
                    value="<?php echo_h(_e("Attach Gallery"))?>"
                    id="save_button"
                    type="submit"
                />
            </div>

        </form>
    </body>
</html>