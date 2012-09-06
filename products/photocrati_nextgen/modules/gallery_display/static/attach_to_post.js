// Provides a function to close the TinyMCE popup window
function close_attach_to_post_window()
{
	parent.tinyMCE.activeEditor.windowManager.close(window);
}

// Activates the attach to post screen elements
jQuery(function($){
	// Activate horizontal tabs
	$('#attach_to_post_tabs').tabs();

	// Activate accordion for display tab
	$('.accordion').accordion({ clearStyle: true, autoHeight: false });

	// Resize iframes to be height of the content
	$('iframe').load(function(){
		var iframe = this;
		$(iframe).data('parent_resizing', {
			last_height:	iframe.contentWindow.outerHeight,
			iteration:		0
		});

		// Define a callback that will get executed over and over
		// till the final height has been determined
		var callback = function(iframe){
			var data			= $(iframe).data('parent_resizing');
			var current_height	= iframe.contentWindow.outerHeight;
			var exec_callback	= true;

			// If the last determined height differs from the current
			// height, then we re-call this callback to see if the
			// height is still growing
			if ((data.last_height != current_height) && current_height == 0) {
				data.iteration = 0;
				data.last_height = current_height;
				$(iframe).data('parent_resizing', data);
				exec_callback = true;

			}

			// If the height is the same and has been so for the last three calls,
			// then we won't run the procedure any longer and pursue setting
			// the iframe height
			else if (data.iteration >= 3) {
				exec_callback = false;
			}

			// The height is the same, but it could possibly still be growing
			else {
				data.iteration += 1;
				$(iframe).data('parent_resizing', data);
			}

			// Execute the callback
			if (exec_callback)
				setTimeout(function(){
					callback(iframe);
				}, 200);

			// Set the iframe height
			$(iframe).height(current_height ? current_height : 100);
		};

		// Determine the iframe height
		setTimeout(function(){
			callback(iframe);
		}, 5);
	});

	// Close the window when the escape key is pressed
	$(this).keydown(function(e){
		if (e.keyCode == 27) close_attach_to_post_window();
		return;
	});
});