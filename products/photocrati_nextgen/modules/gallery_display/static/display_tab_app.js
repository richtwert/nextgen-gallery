
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
NggDisplayTab.displayed_gallery				= Em.Object.create({
	source:					'',
	containers:				[],
	entities:				[],
	display_type_id:		false,

	/**
	 * Returns the ID of the selected source
	 */
	source_id:				function(){
		var source = this.get('source');
		if (source)
			return source.get('id');
		else
			return null;
		return this.get('source').get('id');
	}.property('source'),


	/**
	 * Returns an array of container ids
	 */
	container_ids:			function(){
		return this.get('containers').getEach('id');
	}.property('containers.@each.length'),


	/**
	 * Returns a string of container titles
	 */
	container_titles:		function(){
		var titles = this.get('containers').join(', ');
		if (titles) {
			var index = titles.lastIndexOf(', ');
			titles = titles.slice(0, index);
			titles += ", and "+this.get('containers')[0];
			return titles;
		}
		return titles;
	}.property('containers.@each.length'),



	/** Returns the name of the entity types associated with this
	 *  attached gallery
	 */
	entity_type:			function(){
		$retval = 'images';
		switch(this.get('source_id')) {
			case 'album':
			case 'albums':
				$retval = 'galleries';
				break;
		}
		return $retval;
	}.property('source'),

	/**
	 * Returns an array of entity ids
	 */
	entity_ids:				function(){
		return this.get('entities').getEach('id');
	}.property('entities.@each.length'),


	/**
	 * Gets excluded entities
	 */
	excluded_entities:		function(){
		return this.get('entities').filterProperty('exclude', true);
	}.property('entities.@each.exclude'),


	/**
	 * Gets the ids of all excluded entities
	 */
	excluded_entity_ids:	function(){
		return this.get('excluded_entities').getEach('id');
	}.property('excluded_entities.@each.length'),


	/**
	 * Gets included entities
	 */
	included_entities:		function(){
		return this.get('entities').filterProperty('exclude', false);
	}.property('entities.@each.exclude'),


	/**
	 * When the source is changed, we add the associated template
	 * to the DOM
	 */
	_source_Changed:		Ember.observer(function(){
		 var view = NggDisplayTab.get('attached_source_view');
		 if (view) view.remove();
		 var source_id = this.get('source_id');
		 if (source_id) {
			 var view_name = source_id+'_source_view';
			 var view = NggDisplayTab.get(view_name);
			 view.set('templateName', view_name);
			 NggDisplayTab.set('attached_source_view', view);
			 view.appendTo('#source_configuration');
		 }
	}).observes('source'),


	/**
	 * When the container id is changed, we update the list
	 * of images or albums we're displaying
	 */
	_container_ids_Changed: Ember.observer(function(){

		NggDisplayTab.preview_view.remove();

		if (this.get('source_id') != 'albums') {
			if (this.get('containers').length > 0) {
				this.fetch_gallery_images()
				NggDisplayTab.preview_view.appendTo('#preview_tab_content');
			}
		}
	}).observes('containers'),


	/**
	 * Finds an entity by it's ID
	 */
	get_entity_by_id:		function(id){
		return this.get('entities').findProperty('id', id);
	},


	/**
	 * Fetches images from a selected list of galleries
	 */
	fetch_gallery_images:	function(offset, limit){

		// Set default parameters
		if (typeof limit != "number") {
			offset	= 0;
			limit	= 0;
		}

		// Create request
		var self = this;
		var request = {
			action:	'get_displayed_gallery_images',
			displayed_gallery: {
				source:			self.get('source_id'),
				container_ids:	self.get('container_ids'),
				exclusions:		self.get('excluded_entity_ids')
			}
		};
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response = JSON.parse(response);

			// If no error...
			if (typeof response.error == 'undefined') {

				// Reset the entities array.
				// TODO: This is probably not the best way of handling this.
				// If a user selects "Gallery 1", makes some exclusions, and then
				// selects "Gallery 2", the exclusions will get reset
				self.set('entities', []);
				response.images.forEach(function(item){
					var image = Ember.Object.create(item);
					image.set('id', image[image.get('id_field')]);
					image.set('exclude', image.get('exclude') == 0 ? false : true);
					self.get('entities').pushObject(image);
				});

				// If we haven't retrieved all of the images,
				// and the "source" selected is still "galleries",
				// then we continue to fetch galleries
				if (response['offset'] < response['total'] && self.get('source_id') != 'albums') {
					self.fetch_gallery_images(response['offset']+response['limit'], response['limit']);
				}
			}
		});
	}
});


/************************************************************
 * Gets the view used to render source configuration fields
 * for the "galleries" source
 */
NggDisplayTab.galleries_source_view		= Ember.View.create({
	tagName:			'tbody',
	source_idBinding:	'NggDisplayTab.displayed_gallery.source_id',
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
				 var width = jQuery('#existing_galleries_chzn').width(400).width();
				 jQuery('#existing_galleries_chzn .search-field input').width(width);
				 jQuery('#existing_galleries_chzn .chzn-drop').width(width-2);

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
			if (response['offset'] < response['total'] && self.get('source_id') == 'galleries') {
				self.fetch_galleries(response['offset']+response['limit'], response['limit']);
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


/**
 * Represents the view for displaying the image/gallery preview area
 */
NggDisplayTab.preview_view = Ember.View.create({
	templateName:				'preview_area',
	entitiesBinding:			'NggDisplayTab.displayed_gallery.entities',
	displayed_galleryBinding:	'NggDisplayTab.displayed_gallery',
	didInsertElement:			function(){

		// Enable sorting!
		var last_offset = 0;
		jQuery('#preview_entity_list').sortable({
			axis:	'y',
			opacity: 0.7,
			items:	'li:not(.header)',
			containment: 'parent'
		}).bind('sort', function(event, ui){
			var direction = ui.offset.top > last_offset ? 'down' : 'up';
			var win_height = jQuery(window).height();
			var doc_height = jQuery(document).height();
			ui.offset.bottom = doc_height - ui.offset.top;

			// Determine if the user is scrolling down
			if (direction == 'down' && win_height + window.scrollY >= ui.offset.top) {

				// Calculate how to autoscroll
				if (jQuery(window).height() - ui.offset.top <= ui.item.height()) {
					window.scrollBy(0, ui.item.height()/15);
				}
			}

			// Determine if the user is scrolling up
			else if (direction == 'up' && ui.offset.top <= window.scrollY) {

				// Calculate how to autoscroll
				if (jQuery(window).height() - jQuery(document).height() - ui.offset.top <= ui.item.height()) {
					window.scrollBy(0, ui.item.height()/15*-1);
				}
			}

			last_offset = ui.offset.top;
		});
	},

	/**
	 * Provides an exclude button for a particular entities in the preview area
	 */
	ExcludeButton:	Ember.View.extend({
		tagName:					'input',
		type:						'checkbox',
		classBindings:				['checked'],
		attributeBindings:			['checked', 'value', 'type'],
		displayed_galleryBinding:	'parentView.displayed_gallery',

		/**
		 * Determines if the entity is included or excluded
		 */
		checked:					function(){

			var retval = false;
			var item = this.get('displayed_gallery').get_entity_by_id(this.get('value'));
			return typeof(item) != 'undefined' && item.exclude == true ? true : false;
		}.property('displayed_gallery.excluded_entities.@each.length', 'value'),


		/**
		 * Includes/excludes an entity
		 */
		click:						function(e){
			var item = this.get('displayed_gallery').get_entity_by_id(this.get('value'));
			if (item) {
				item.set('exclude', e.currentTarget.checked);
			}
		}
	}),

	/**
	 * Provides a button to exclude all entities
	 */
	ExcludeAllButton: Ember.View.extend({
		tagName:			'input',
		type:				'checkbox',
		attributeBindings:	['type'],
		entitiesBinding:	'parentView.displayed_gallery.entities',
		click:				function(e){
			this.get('entities').setEach('exclude', e.currentTarget.checked);
		}
	})
});


/**
 * Provides a radio button widget for Ember
 */
Ember.RadioButton = Ember.View.extend({
  classNames:			['ember-radio-button'],
  attributeBindings:	['type', 'checked', 'value', 'name'],
  tagName:				'input',
  type:					'radio',
  name:					"radio_button",
  checked:				function(){
	  return this.get('value') == this.get('selection');
  }.property('value', 'selection'),
  change: function(){
	  this.set('selection', this.get('value'));
  }
});