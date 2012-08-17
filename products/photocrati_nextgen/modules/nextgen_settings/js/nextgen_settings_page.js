jQuery(function($){
	$('#watermark_colorpicker').farbtastic('#font_color');

	// Activate accordions
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });

	// When the lightbox library is changed, display it's properties
	$('#lightbox_library').change(function(){
		var selected = $(this).find(':selected');
		$('#lightbox_library_code').val(selected.attr('code'));
		$('#lightbox_library_stylesheets').val(selected.attr('css_stylesheets'));
		$('#lightbox_library_scripts').val(selected.attr('scripts'));
	}).change();

	// Configure the watermark customization link
	$('#watermark_customization').attr('rel', 'watermark_'+$('#watermark_source').val()+'_source');

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

	// Toggle the advanced settings
	$('.advanced_toggle_link').click(function(e){
		e.preventDefault();
		var form_id = '#'+$(this).attr('rel');
		var btn = $(this);
		$(form_id).toggle(500, 'swing', function(){
			if ($(this).hasClass('hidden')) {
				$(this).removeClass('hidden');
				btn.text(btn.attr('active_label'));
			}
			else {
				$(this).addClass('hidden');
				btn.text(btn.attr('hidden_label'));
			}
		});
	});
});