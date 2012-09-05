/************************************************************
 * Define the application
 */
var NggDisplayTab = Em.Application.create({
	/**
	 * The available image/gallery sources
	 */
	sources:						[],


	/**
	 * The currently displayed source view
	 */
	attached_source_view:			null,


	/**
	 * The galleries available in NextGen Gallery
	 */
	galleries:	[],


	/**
	 * Populates all existing data
	 */
	ready:							function(){
		this.fetch_sources();
	},


	/**
	 * Saves the displayed gallery
	 */
	save:							function(e){
		// Initiate the request to save...
		var request = {
			id:					NggDisplayTab.displayed_gallery.get('id'),
			displayed_gallery:	{
				source:			NggDisplayTab.displayed_gallery.get('source_id'),
				container_ids:	NggDisplayTab.displayed_gallery.get('container_ids'),
				entity_ids:		NggDisplayTab.displayed_gallery.get('included_entity_ids'),
				display_type:	NggDisplayTab.displayed_gallery.get('display_type')
			},
			action:				'save_displayed_gallery'
		};
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response  = JSON.parse(response);

			// Alert any errors
			if (response.error) alert(error);

			// Display validation errors
			else if (response.validation_errors) jQuery('#displayed_tab').prepend(response.validation_errors);

			// Succesful save...
			else if (response.displayed_gallery) {

				// Update the ID of the displayed gallery
				var id_field = response.displayed_gallery.id_field;
				var id = response.displayed_gallery[id_field];
				NggDisplayTab.displayed_gallery.set('id', id);

				// Insert placeholder into tinyMCE content area and close window
				var editor = parent.tinyMCE.activeEditor;
				NggDisplayTab.displayed_gallery.entities[0];
				var preview_url = ngg_displayed_gallery_preview_url + '?id='+id;
				var snippet = "<img class='ngg_displayed_gallery' src='"+preview_url+"'/>";
				if (editor.getContent().indexOf(preview_url) < 0)
					editor.execCommand('mceInsertContent', false, snippet);
				close_attach_to_post_window();
			}
		});
	},


	/**
	 * Fetches a list of image/gallery sources to be used in the Attach To Post
	 * interface
	 */
	fetch_sources:					function(){
		var app = this;
		var request = {
			action:	'get_attach_to_post_sources'
		};
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response  = JSON.parse(response);
			if (response.sources) {
				response.sources.forEach(function(item){
					app.get('sources').pushObject(item);
					if (item.id == existing.source) app.get('displayed_gallery').set('source', item);
				});
			}
		});
	},

	/**
	 * Fetches entities to be used with an attached gallery. Can be used as
	 * a cursor, fetching chunks of the result if a limit and offset are specified
	 */
	fetch_entities:					function(obj_container, container_id_field, params, offset, limit){
		// Set default parameters
		if (typeof limit != "number") {
			offset	= 0;
			limit	= 0;
		}

		// Create request
		var self = this;
		var request = {
			action:	'get_displayed_gallery_images',
			displayed_gallery: params
		};
		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response = JSON.parse(response);

			// If no error, then add the images to the displayed gallery
			if (typeof response.error == 'undefined') {
				response.images.forEach(function(item){
					var image = Ember.Object.create(item);
					image.set('id', image[image.get('id_field')]);
					image.set('container_id', image.get(container_id_field));
					image.set('exclude', image.get('exclude') == 0 ? false : true);
					obj_container.pushObject(image);
				});

				// If we haven't retrieved all of the images,
				// and the "source" selected is still "galleries",
				// then we continue to fetch galleries
				if (response['offset'] < response['total'] && self.get('source_id') != 'albums') {
					self.fetch_entities(obj_container, container_id_field, params, response['offset']+response['limit'], response['limit']);
				}
			}
		});
	},


	/**
	 * Fetches images for a particular gallery
	 */
	fetch_gallery_images:			function(obj_container, gallery_id, offset, limit){
		this.fetch_entities(
			obj_container,
			'galleryid',
			{source: 'galleries', container_ids: [gallery_id]},
			offset,
			limit
		);
	}
});


/************************************************************
* The associated attached gallery
*/
NggDisplayTab.displayed_gallery				= Em.Object.create({
	id:							0,
	source:						'',
	containers:					Ember.A(),
	galleriesBinding:			'NggDisplayTab.galleries',
	sourcesBinding:				'NggDisplayTab.sources',
	previous_container_ids:		Ember.A(),
	entities:					Ember.A(),
	display_type:				false,

	/**
	 * Initializes the object
	 */
	init:						function(){
		this._super();

		// If we're editing an existing displayed gallery, then
		// update the model
		if (existing != null) {
			this.set('id', existing.id);
			this.set('display_type',  existing.display_type);
			this.set('display_settings', Ember.Object.create(existing.display_settings));

			// Update containers
			var containers	= this.get('containers');
			var galleries	= this.get('galleries');
			existing.container_ids.forEach(function(item){
				var item = Ember.Object.create({
					id: item,
					title: ''
				});
				containers.pushObject(item);
				galleries.pushObject(item);

			});

			this.fetch_images();
		}

		// Adds an observer for 'containers' to get it's value and assign to
		// 'previous_containers' before it's value get's changed
		Ember.addBeforeObserver(this, 'containers', this, '_set_previous_containers');
	},

	/**
	 * Returns the ID of the selected source
	 */
	source_id:					function(){
		var source = this.get('source');
		if (source)
			return source.id
		else
			return null;
	}.property('source'),


	/**
	 * Returns an array of container ids
	 */
	container_ids:				function(key, value){
		return this.get('containers').getEach('id');
	}.property('containers.@each.length'),


	/**
	 * Returns a string of container titles
	 */
	container_titles:			function(){
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
	entity_type:				function(){
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
	entity_ids:					function(){
		return this.get('entities').getEach('id');
	}.property('entities.@each.length'),


	/**
	 * Gets excluded entities
	 */
	excluded_entities:			function(){
		return this.get('entities').filterProperty('exclude', true);
	}.property('entities.@each.exclude'),


	/**
	 * Gets the ids of all excluded entities
	 */
	excluded_entity_ids:		function(){
		return this.get('excluded_entities').getEach('id');
	}.property('excluded_entities.@each.length'),


	/**
	 * Gets included entities
	 */
	included_entities:			function(){
		return this.get('entities').filterProperty('exclude', false);
	}.property('entities.@each.exclude'),


	/**
	 * Gets the IDs of the included entities
	 */
	included_entity_ids:		function(){
		return this.get('included_entities').getEach('id');
	}.property('included_entities.@each.length'),


	/**
	 * Gets the added/removed container ids since the containers property was
	 * changed
	 */
	container_difference:		function(){
		var previous = this.get('previous_container_ids');
		var current	 = this.get('container_ids');
		var retval = {
			additions:	[],
			removals:	[]
		};

		// Calculate additions
		current.forEach(function(item){
			if (previous.indexOf(item) < 0) retval.additions.push(item);
		});

		// Calculate removals
		previous.forEach(function(item){
			if (current.indexOf(item) < 0) retval.removals.push(item);
		});

		return retval;
	}.property('containers.@each.length'),


	/**
	 * When the source is changed, we add the associated template
	 * to the DOM
	 */
	_source_Changed:			Ember.observer(function(){
		 var view = NggDisplayTab.get('attached_source_view');
		 if (view) view.remove();
		 var source_id = this.get('source_id');
		 if (source_id) {
			 var view_name = source_id+'_source_view';
			 var view = this.get(view_name);
			 view.set('templateName', view_name);
			 NggDisplayTab.set('attached_source_view', view);
			 view.appendTo('#source_configuration');
		 }
	}).observes('source'),


	/**
	 * Sets the 'previous_containers' property to the 'containers' value
	 * before it's value changes
	 */
	_set_previous_containers:	function(){
		var current = this.get('container_ids');
		this.set(
			'previous_container_ids',
			typeof(current) == 'undefined' ?
				Ember.A() : current
		);
	},


	/**
	 * When the container id is changed, we update the list
	 * of images or albums we're displaying
	 */
	_containersChanged:		Ember.observer(function(){
		NggDisplayTab.preview_view.remove();
		if (this.get('containers').length > 0) {
			// We then call a method to handle the logic of updating the
			// entities. We do this for extensibility - a module can simply
			// monkey patch this object
			var method = '_update_entities_for_'+this.get('source_id');
			this[method]();
			NggDisplayTab.preview_view.appendTo('#preview_tab_content');
		}
	}).observes('containers'),


	/**
	 * Finds an entity by it's ID
	 */
	get_entity_by_id:		function(id){
		return this.get('entities').findProperty('id', id);
	},


	/**
	 * Removes entities that have the specified container
	 */
	remove_entities:		function(container_id){
		var found = true;
		while (found) {
			found = false;
			var entities = this.get('entities');
			for (var i=0; i<entities.length; i++) {
				var item = entities[i];
				if (item.container_id == container_id) {
					entities.removeAt(i);
					found = true;
					break;
				}
			}
		}
	},


	/**
	 * Fetches images from a selected list of galleries
	 */
	fetch_images:	function(offset, limit){
		var self = this;
		NggDisplayTab.fetch_entities(
			this.get('entities'),
			'galleryid',
			existing,
			offset,
			limit
		);
	},

	/**
	 * The list of containers changed. Adjust what entities are present
	 */
	_update_entities_for_galleries:	function() {
		var self = this;
		var diff = this.get('container_difference');
		diff.additions.forEach(function(id){
			NggDisplayTab.fetch_gallery_images(
				self.get('entities'),
				id
			);
		});
		diff.removals.forEach(function(id){
			self.remove_entities(id);
		});
	}
});


/************************************************************
* Gets the view used to render source configuration fields
* for the "galleries" source
*/
NggDisplayTab.displayed_gallery.galleries_source_view = 	Ember.View.create({
	tagName:					'tbody',
	containersBinding:			'NggDisplayTab.displayed_gallery.containers',
	source_idBinding:			'NggDisplayTab.displayed_gallery.source_id',
	galleriesBinding:			'NggDisplayTab.galleries',
	galleriesChanged:			function(){
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
				item = Ember.Object.create(item);
				item.set('id', item[item.id_field].toString());
				var existing_gallery = self.get('galleries').findProperty('id', item.get('id'));
				if (!existing_gallery) self.get('galleries').pushObject(Ember.Object.create(item));
				else for (var key in item) existing_gallery.set(key, item[key]);
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
		var limit = NggDisplayTab.get('galleries').length;
		if (existing != null) limit -= existing.container_ids.length;
		this.fetch_galleries(limit, 25);

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
		entitiesBinding:			'parentView.displayed_gallery.entities',

		/**
		 * Determines if the entity is included or excluded
		 */
		checked:					function(key, value){
			var retval = false;
			var item = this.get('displayed_gallery').get_entity_by_id(this.get('value'));
			return typeof(item) != 'undefined' && item.exclude == true ? true : false;
		}.property('displayed_gallery.excluded_entities.@each.length', 'value'),


		/**
		 * Includes/excludes an entity
		 */
		click:						function(e){
			var item = this.get('entities').findProperty('id', this.get('value'));
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
		attributeBindings:	['type','id'],
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