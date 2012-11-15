// Provides a function to close the TinyMCE popup window
function close_attach_to_post_window()
{
	parent.tinyMCE.activeEditor.windowManager.close(window);
}

// Adjusts the height of a frame on the page, and then executes
// the specified callback
function adjust_height_for_frame(frame, callback)
{
	var current_height	= jQuery(frame.contentDocument).height();
	jQuery(frame).height(current_height);
	if (callback != undefined)
		return callback.call(frame, current_height);
	else
		return true;
}

// Activates the attach to post screen elements
jQuery(function($){
	// Activate horizontal tabs
	$('#attach_to_post_tabs').tabs();

	// Activate accordion for display tab
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });
	$('.ui-tabs-nav a').click(function(e){
		if ($(e.srcElement).parent().hasClass('ui-state-active')) {
			var iframe = $(e.srcElement.hash+' iframe');
			if (iframe.length > 0) {
				if (iframe[0].contentDocument.location != iframe.attr('src')) {
					iframe[0].contentDocument.location = iframe.attr('src');
				}
			}
		}
	});

	// Close the window when the escape key is pressed
	$(this).keydown(function(e){
		if (e.keyCode == 27) close_attach_to_post_window();
		return;
	});
});