jQuery(function($){
	// Activate accordions
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });

	/**** LIGHTBOX EFFECT TAB ****/

	// When the lightbox library is changed, display it's properties
	$('#lightbox_library').change(function(){
		var selected = $(this).find(':selected');
		$('#lightbox_library_code').val(selected.attr('code'));
		$('#lightbox_library_stylesheets').val(selected.attr('css_stylesheets'));
		$('#lightbox_library_scripts').val(selected.attr('scripts'));
	}).change();

	/**** WATERMARK TAB ****/

	// Activate the color picker for the Watermark font color
	$('#watermark_colorpicker').farbtastic('#font_color');

	// Configure the watermark customization link
	$('#watermark_customization').attr('rel', 'watermark_'+$('#watermark_source').val()+'_source');

	// Configure the button to switch from watermark text to image
	$('#watermark_source').change(function(){
		$('#'+$('#watermark_customization').attr('rel')).css('display', '').addClass('hidden');
		if (!$('#'+$(this).val()).hasClass('hidden')) {
			$('#'+$(this).val()).removeClass('hidden');
		}
		$('#watermark_customization').attr('rel', 'watermark_'+$('#watermark_source').val()+'_source').click();
	});

    // sends the current settings to a special ajax endpoint which saves them, regenerates the url, and then reverts
    // to the old settings. this submits the form and forces a refresh of the image through the time parameter
    $('#nextgen_settings_preview_refresh').click(function(event) {
        event.preventDefault();
        var form = $(this).parents('form:first');
        var self = $(this);
        $.ajax({
            type: form.attr('method'),
            url: $(this).data('refresh-url'),
            data: form.serialize(),
            dataType: 'json',
            success: function(data) {
                var img = self.prev();
                var src = data.thumbnail_url;
                queryPos = src.indexOf('?');
                if (queryPos != -1) {
                    src = src.substring(0, queryPos);
                }

                img.attr('src', src + '?' + new Date().getTime());
            }
        });
    });
	/**** STYLES TAB ****/

	// When the selected stylesheet changes, fetch it's contents
	$('#activated_stylesheet').change(function(){
		var selected = $(this).find(':selected');
		var data = {
			action:		'get_stylesheet_contents',
			cssfile:	selected.val()
		};
		$.post(photocrati_ajax_url, data, function(res){
			if (typeof res !== 'object') res = JSON.parse(res);
			$('#cssfile_contents').val(res.error ? res.error : res.contents);
			var status = $('#writable_identicator');
			if (res.writable) status.text(status.attr('writable_label'));
			else status.text(status.attr('readonly_label'));
		});
	}).change();
});