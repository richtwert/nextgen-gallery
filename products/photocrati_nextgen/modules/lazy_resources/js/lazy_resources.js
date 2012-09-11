var Lazy_Resources = {
	script_urls:	[],
	style_urls:		[],

	// Enqueues all styles and scripts, using lazy loading
	enqueue:		function(){
		jQuery(function($){
			Lazy_Resources.enqueue_scripts();
			Lazy_Resources.enqueue_styles();
		});
	},

	// Lazy loads all scripts. Must be called after JQuery has initialized
	// and ready() event has fired
	enqueue_scripts:	function(){
		Sid.js(this.script_urls);
		this.style_urls = [];
	},

	// Lazy loads all styles. Must be called after JQuery has initialized
	// and ready() event has fired
	enqueue_styles:		function(){
		Sid.css(this.style_urls);
		this.style_urls = [];
	}
};