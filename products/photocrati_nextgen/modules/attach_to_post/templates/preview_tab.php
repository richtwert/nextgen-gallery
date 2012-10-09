<script type="text/x-handlebars" data-template-name="preview_area">

{{#collection "sortedEntityView" itemClassNames="previewed_entity"}}
    <div class="container">
        {{view ExcludeButton class="inclusion_checkbox" valueBinding="view.content.id"}}
        <div class="image_container">
            <img
                {{bindAttr id="view.content.id" src="view.content.thumb_url" alt="view.content.alttext" title="view.content.title" width="view.content.thumb_size.width" height="view.content.thumb_size.height"}}
            />
        </div>
    </div>
    <br class="clear"/>
{{/collection}}
</script>

<script type="text/x-handlebars" data-template-name="no_entities_available">
	<p>There is nothing to preview</p>
</script>

<script type="text/x-handlebars" data-template-name="preview_not_supported">
	<p>A preview cannot be generated for this source.</p>
</script>