<?php
$obj = method_exists($this, 'get_class_definition_dir') ? $this : $this->object;
$template_dir = path_join($obj->get_class_definition_dir(), 'templates');
$default_template_dir = MVC_TEMPLATE_DIR;
?>

<h1>Welcome to Pope MVC!</h1>
<p>
    You have not yet created a index.php file in:<br/><strong><?php echo $template_dir; ?></strong>
</p>
<p>
    So, you're being served the index.php from the default directory:<br/>
    <strong><?php echo $default_template_dir ?></strong>
</p>