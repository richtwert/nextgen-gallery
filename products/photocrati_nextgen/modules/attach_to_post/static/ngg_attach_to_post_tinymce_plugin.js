// Self-executing function to create and register the TinyMCE plugin
(function(siteurl) {

	// Create the plugin. We'll register it afterwards
	tinymce.create('tinymce.plugins.NextGEN_AttachToPost', {

		/**
		 * The WordPress Site URL
		**/
		siteurl:	siteurl,

		/**
		 * Returns metadata about this plugin
		 */
		getInfo: function() {
			return {
				longname:	'NextGen - Attach Gallery',
				author:		'Photocrati Media',
				authorurl:	'http://www.photocrati.com',
				infourl:	'http://www.nextgen-gallery.com',
				version:	'0.1'
			};
		},


		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 */
		init: function(editor, plugin_url) {

			// Register a new TinyMCE command
			editor.addCommand('ngg_attach_to_post', this.render_attach_to_post_interface, {
				editor: editor,
				plugin:	editor.plugins.NextGEN_AttachToPost
			});

			// Add a button to trigger the above command
			editor.addButton('NextGEN_AttachToPost', {
				title:	'NextGEN Gallery - Attach To Post',
				cmd:	'ngg_attach_to_post',
				image:	plugin_url+'/nextgen.gif'
			});

			// When the shortcode is clicked, open the attach to post interface
			editor.settings.extended_valid_elements += ",shortcode";
			editor.settings.custom_elements = "shortcode";
			var self = this;
            var drag_in_progress = false;
            var click_timer;

            editor.onMouseDown.addToTop(function(editor, e) {
                if (e.target.tagName == 'IMG' && e.target.className == 'ngg_displayed_gallery') {
                    click_timer = setTimeout(function() {
                        drag_in_progress = true;
                    }, 250);
                }
            });

            editor.onMouseUp.addToTop(function(editor, e) {
				if (!drag_in_progress && e.target.tagName == 'IMG' && e.target.className == 'ngg_displayed_gallery') {
					editor.dom.events.cancel(e);
					editor.dom.events.stop(e);
					var id = e.target.src.match(/\d+$/);
					if (id) id = id.pop();
					var obj = tinymce.extend(self, {
						editor: editor,
						plugin: editor.plugins.NextGEN_AttachToPost,
						id:		id
					});
					self.render_attach_to_post_interface.call(obj);
				}
                clearTimeout(click_timer);
                drag_in_progress = false;
				return false;
			});
		},


		/**
		 * Renders the attach to post interface
		 */
		render_attach_to_post_interface:	function(id) {

			// Determine the attach to post url
			var attach_to_post_url = this.plugin.siteurl+'/wp-admin/attach_to_post';
			if (typeof(this.id) != 'undefined') {
				attach_to_post_url += "?id="+this.id;
			}

			// We're going to open a dialog window. TinyMCE doesn't
			// get the positioning exactly right, so we add an event
			// handler to make adjustments
			//
			// We also make the parent window unscrollable, to avoid
			// multiple scrollbars
			this.editor.windowManager.onOpen.add(function(win){
				jQuery('html,body').css('overflow', 'hidden');
			});

			// Restore scrolling for the main content window
			// when the attach to post interface is closed
			this.editor.windowManager.onClose.add(function(win){
				jQuery('html,body').css('overflow', 'auto');
			});

			// Open a window, occupying 90% of the screen real estate
			this.editor.windowManager.open({
				file:	attach_to_post_url,
				width:	1200,
				height:	600,
				inline: true,
				title:	"NextGEN Gallery - Attach To Post"
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('NextGEN_AttachToPost', tinymce.plugins.NextGEN_AttachToPost);
})(wp_site_url);
