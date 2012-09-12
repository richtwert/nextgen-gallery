jQuery(function($){
	// Activate customize link
	$('.ngg_customize_thumbnails').click(function(e){
		e.preventDefault();
		var btn = $(this);
		var id = btn.attr('id');
		var related = $('.customize_thumbnail_dimensions[rel="'+id+'"]');
		related.toggle(500, 'swing', function(){
			if (related.hasClass('hidden')) {
				related.removeClass('hidden');
				btn.text(btn.attr('active_label'));
			}
			else {
				related.addClass('hidden');
				btn.text(btn.attr('hidden_label'));
			}
		});
	});

	// Register change event
	$('.ngg_thumbnail_dimensions').live('change', function(){
		var id = $(this).attr('id');
		var dimensions = $(this).find('option:selected').val().split(/x/);
		var width = dimensions[0];
		var height = dimensions[1];
		$('.ngg_thumbnail_dimension_width[rel="'+id+'"]:first').val(width);
		$('.ngg_thumbnail_dimension_height[rel="'+id+'"]:first').val(height);
	}).change();
});