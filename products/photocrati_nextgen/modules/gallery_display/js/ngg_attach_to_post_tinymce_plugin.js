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
				image:	'http://www.mricons.com/store/png/110948_27864_24_gallery_image_landscape_photo_icon.png'
			});
		},


		/**
		 * Renders the attach to post interface
		 */
		render_attach_to_post_interface:	function() {

			// We're going to open a dialog window. TinyMCE doesn't
			// get the positioning exactly right, so we add an event
			// handler to make adjustments
			//
			// We also make the parent window unscrollable, to avoid
			// multiple scrollbars
			this.editor.windowManager.onOpen.add(function(win){
				win.features.top = (win.features.top - 3) < 0 ? 1 : 3 ;
				win.features.height -= 3;
				jQuery('html,body').css('overflow', 'hidden');
			});

			// Restore scrolling for the main content window
			// when the attach to post interface is closed
			this.editor.windowManager.onClose.add(function(win){
				jQuery('html,body').css('overflow', 'auto');
			});

			// Open a window, occupying 97% of the screen real estate
			this.editor.windowManager.open({
				file:	this.plugin.siteurl+'/wp-admin/attach_to_post',
				width:	innerWidth * .97,
				height:	(innerHeight * .95),
				inline: true,
				title:	"NextGEN Gallery - Attach To Post"
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('NextGEN_AttachToPost', tinymce.plugins.NextGEN_AttachToPost);
})(wp_site_url);