var Lazy_Resources = {
	script_urls:	[],
	style_urls:		[],

	// Enqueues all styles and scripts, using lazy loading
	enqueue:		function(){
		jQuery(function($){

            // If there are scripts to load, we'll load them first, which will
            // automatically load the styles as well after the scripts are finished
            // loading.
            //
            // If there are no scripts to load, we then proceed to just load the styles
            if (Lazy_Resources.script_urls.length > 0)
                Lazy_Resources.enqueue_scripts();
            else
                Lazy_Resources.enqueue_styles();
		});
	},

	// Lazy loads all scripts. Must be called after JQuery has initialized
	// and ready() event has fired
	enqueue_scripts:	function(){
		Sid.js(this.script_urls, function(){
            Lazy_Resources.enqueue_styles();
        });
		this.script_urls = [];
	},

	// Lazy loads all styles. Must be called after JQuery has initialized
	// and ready() event has fired
	enqueue_styles:		function(){
		Sid.css(this.style_urls, function(){
            setTimeout(function(){
                jQuery(document).trigger('lazy_resources_loaded');
            }, 0);
        });
		this.style_urls = [];
	}
};