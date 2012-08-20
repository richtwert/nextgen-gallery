
/************************************************************
 * Define the application
 */
var NggDisplayTab = Em.Application.create({
	/**
	 * The available image/gallery sources
	 */
	sources: [],


	/**
	 * The currently displayed source view
	 */
	attached_source_view:	null,


	/**
	 * The galleries available in NextGen Gallery
	 */
	galleries:	[],


	/**
	 * Fetches a list of image/gallery sources to be used in the Attach To Post
	 * interface
	 */
	fetch_sources:				function(){
		var app = this;
		var request = {
			action:	'get_attach_to_post_sources'
		};
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response  = JSON.parse(response);
			if (response.sources) {
				response.sources.forEach(function(item){
					app.get('sources').pushObject(Ember.Object.create(item));
				});
			}
		});
	},


	/**
	 * Initializes the application
	 */
	ready:						function(){
		this.fetch_sources();
	}
});

/************************************************************
* The associated attached gallery
*/
NggDisplayTab.attached_gallery				= Em.Object.create({
   source:			'',
   container_ids:	[],

   /**
	* When the source is changed, we add the associated template
	* to the DOM
	*/
   _sourceChanged:	Ember.observer(function(){
		var view = NggDisplayTab.get('attached_source_view');
		if (view) view.remove();
		var source = this.get('source');
		if (source) {
			var view_name = source.get('id')+'_source_view';
			var view = NggDisplayTab.get(view_name);
			view.set('templateName', view_name);
			NggDisplayTab.set('attached_source_view', view);
			view.appendTo('#source_configuration');
		}
   }).observes('source')
});


/************************************************************
 * Gets the view used to render source configuration fields
 * for the "galleries" source
 */
NggDisplayTab.galleries_source_view		= Ember.View.create({
	tagName:			'tbody',
	galleriesBinding:	'NggDisplayTab.galleries',
	galleriesChanged:	function(){
		// flush the RunLoop so changes are written to DOM?
		Ember.run.sync();

		// trigger the 'liszt:updated'
		Ember.run.next(this, function() {

			// When the list is rebuilt, adjust the width of the widget,
			// and the height of the accordion tab. Oddly enough, the
			// chosen widget doesn't do this itself yet.
			// See: https://github.com/harvesthq/chosen/issues/533
			jQuery('#existing_galleries').bind('liszt:updated', function(){
				 var $this = jQuery(this);
				 jQuery('#existing_galleries_chzn').width($this.width());
				 jQuery('#existing_galleries_chzn .search-field input').width($this.width());
				 jQuery('#existing_galleries_chzn .chzn-drop').width($this.width()-2);

				 // Update the height of the accordion
				 var dropdown_height = jQuery('#existing_galleries_chzn .chzn-choices').height();
				 var source_tab_height = jQuery('#source_tab_content').height();
			 jQuery('#source_tab_content').height(dropdown_height + source_tab_height);
			}).trigger('liszt:updated');
		});
	}.observes('galleries.@each.id'),

	/**
	 * Fetches galleries from the server in groups of 25
	 */
	fetch_galleries: function(offset, limit) {

		// Set default parameters
		if (typeof limit != "number") {
			offset	= 0;
			limit	= 0;
		}

		// Create AJAX resquest
		var request = {
			action:	'get_existing_galleries',
			offset:	offset,
			limit:	limit
		};
		var self = this;
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response = JSON.parse(response);

			// Add each gallery
			response.galleries.forEach(function(item){
				gallery = Ember.Object.create(item);
				gallery.reopen({
					id:	function(){
						var id_field = this.get('id_field');
						return this.get(id_field);
					}.property(item.id_field)
				});
				self.get('galleries').pushObject(gallery);
			});

			// If we haven't retrieved all of the galleries,
			// and the "source" selected is still "galleries",
			// then we continue to fetch galleries
			if (response['offset'] < response['total']) {
				this.fetch_galleries(response['offset']+response['limit'], response['limit']);
			}
		});
	},

	/**
	 * Executes immediately after appending to the DOM
	 */
	didInsertElement:	function(){

		// Retrieve missing galleries
		this.fetch_galleries(NggDisplayTab.get('galleries').length, 25);

		// Prettify the dropdown using the chosen library
		var chosen = jQuery('.pretty-dropdown').chosen();
	}
});
