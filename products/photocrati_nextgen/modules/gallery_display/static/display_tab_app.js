/************************************************************
 * Define the application
 */
var NggDisplayTab = Em.Application.create({

	sources:						Ember.A(),
	galleries:						Ember.A(),
	image_tags:						Ember.A(),

	/**
	 * The currently displayed source view
	 */
	attached_source_view:			null,


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
	 * Fetch entities from the server in chunks
	 */
	fetch_in_chunks:				function(request, object_name, obj_container, limit, offset, filter, condition, done){
		// Set default parameters
		if (typeof limit != "number") {
			offset	= 0;
			limit	= 0;
		}
		request['limit'] = limit;
		request['offset'] = offset;

		// Ensure we have a reference to this object
		var self = this;

		jQuery.post(photocrati_ajax_url, request, function(response){
			if (typeof response != 'object') response = JSON.parse(response);

			// Get each item from the response, manipulate the item using
			// the filter, and then add to the object container
			response[object_name].forEach(function(item){
				item = Ember.Object.create(item);

				// Default filter is to set the id property
				if (item.get('id_field')) {
					item.set('id', item.get(item.get('id_field')));
				}

				// We'll let a custom filter be applied
				if (filter) {
					var retval = filter.call(self, item);
					if (retval) item = retval;
				}

				// Add the item to a container
				obj_container.pushObject(item);
			});

			// If we haven't retrieved all of the items,
			// then we continue to fetch galleries
			if (response['offset'] <  response['total']) {
				var continue_fetching = true;
				if (condition) {
					if (!condition.call(self, response))
						continue_fetching = false;
				}
				if (continue_fetching) {
					self.fetch_in_chunks(
						request,
						object_name,
						obj_container,
						limit,
						offset,
						filter,
						condition
					);
				}
			}

			// when finished, call the done handler is available
			if (done) done.call(self, response);
		});
	},


	/**
	 * Fetches a list of image/gallery sources to be used in the Attach To Post
	 * interface
	 */
	fetch_sources:					function(){
		this.fetch_in_chunks(
			{action: 'get_attach_to_post_sources'},
			'sources',
			this.get('sources'),
			25,
			0,
			function(item){
				if (existing && item.id == existing.source) this.get('displayed_gallery').set('source', item);
				return item;
			}
		);
	},

	/**
	 * Fetches entities of a displayed gallery or gallery
	 */
	fetch_entities:					function(obj_container, container_id_field, params){
		this.fetch_in_chunks(
			{action:	'get_displayed_gallery_entities', displayed_gallery: params},
			'entities',
			obj_container,
			25,
			0,
			function(item){
				item.set('container_id', item.get(container_id_field));
				item.set('exclude', item.get('exclude') == 0 ? false : true);
				return item;
			},
			function(){
				return this.get('source_id') != 'albums'
			}
		);
	},

	/**
	 * Fetches images for a particular gallery
	 */
	fetch_gallery_images:			function(obj_container, gallery_id){
		this.fetch_entities(
			obj_container,
			'galleryid',
			{source: 'galleries', container_ids: [gallery_id]},
			25,
			0
		);
	},

	fetch_image_tag_images:			function(){
		this.fetch_entities(
			this.displayed_gallery.get('entities'),
			'term_id',
			{source: 'image_tags', container_ids:	this.displayed_gallery.get('container_ids')},
			25,
			0
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
	image_tagsBinding:			'NggDisplayTab.image_tags',
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

			// Update containers. We update both the selected containers
			// for the displayed gallery, as well as the source of containers.
			// We let a method handle that so that this can be extended
			var containers			= this.get('containers');
			var method				= 'push_to_'+existing.source;
			var self = this;
			existing.container_ids.forEach(function(item){
				var item = Ember.Object.create({
					id: item.toString(),
					title: ''
				});
				self[method].call(self, item);
				containers.pushObject(item);
			});
			console.log("Adding preselected values");

			this.fetch_images();
		}

		// Adds an observer for 'containers' to get it's value and assign to
		// 'previous_containers' before it's value get's changed
		Ember.addBeforeObserver(this, 'containers', this, '_set_previous_containers');
	},

	/**
	 * Used by init() to preload an existing displayed gallery with galleries
	 */
	push_to_galleries:			function(item){
		this.get('galleries').pushObject(item);
	},

	push_to_image_tags:			function(item){
		this.get('image_tags').pushObject(item);
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
		if (NggDisplayTab.preview_view) NggDisplayTab.preview_view.remove();
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
	fetch_images:	function(){
		NggDisplayTab.fetch_entities(
			this.get('entities'),
			'galleryid',
			existing,
			25,
			0
		);
	},

	/**
	 * The list of containers changed. Adjust what entities are present
	 * based on the selected galleries
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
	},

	/**
	 * The list of containers changed. Adjust what entries are present
	 * based on the selected image tags
	 */
	_update_entities_for_image_tags: function(){
		this.set('entities', Ember.A());
		NggDisplayTab.fetch_image_tag_images();
	}
});

Ember.Chosen = Ember.Select.extend({
	tagName:			'select',
	classNameBindings:	['pretty-dropdown'],
	chosen_items:		function(){
		return this.get('content');
	}.property('content.@each.length'),


	/**
	 * Updates the chosen widget to include all options
	 */
	update:								function(){
		this.chosen_items_Changed();
	},

	/**
	 * Observes when the content changes and adjusts the width of the
	 * chosen widget
	 */
	chosen_items_Changed:				function(){

		// flush the RunLoop so changes are written to DOM?
		Ember.run.sync();

		// trigger the 'liszt:updated'
		Ember.run.next(this, function() {

			// When the list is rebuilt, adjust the width of the widget,
			// and the height of the accordion tab. Oddly enough, the
			// chosen widget doesn't do this itself yet.
			// See: https://github.com/harvesthq/chosen/issues/533
			jQuery(this.$()).bind('liszt:updated', function(){
				var chosen_ddl_selector = '#'+jQuery(this).attr('id')+'_chzn';
				var width = jQuery(chosen_ddl_selector).width(400).width();
				jQuery(chosen_ddl_selector+' .search-field input').width('auto');
				jQuery(chosen_ddl_selector+' .chzn-drop').width(width-2);
			}).trigger('liszt:updated');
			console.log('Updating chosen drop-down');

		});
	}.observes('chosen_items'),

	didInsertElement:		function(){
		var parentView = this.get('parentView');
		parentView[this.get('fillCallback')].call(this, this.get('content'), this);
		var select = jQuery(this.$());
		select.attr('data-placeholder', '--Select--');
		jQuery(this.$()).chosen();
		console.log('Creating chosen drop-down');
	}
});


/************************************************************
* Gets the view used to render source configuration fields
* for the "galleries" source
*/
NggDisplayTab.displayed_gallery.galleries_source_view = 	Ember.View.create({
	tagName:			'tbody',
	templateName:		'galleries_source_view',
	fetch_galleries: function(collection, chosen_ddl) {
		collection.clear();
		NggDisplayTab.fetch_in_chunks(
			{action: 'get_existing_galleries'},
			'galleries',
			collection,
			25,
			0,
			function(item){
				var arr = this.get('displayed_gallery').get('containers').map(function(obj, index, arr){
					return (item.get('id') == obj.get('id')) ? item : obj
				});
				this.get('displayed_gallery').set('containers', arr);

				return item;
			},
			function(){
				return this.get('source_id') == 'galleries';
			},
			function(){
				chosen_ddl.update();
			}
		);
	}
});


/************************************************************
* Gets the view used to render source configuration fields
* for the "image_tags" source
*/
NggDisplayTab.displayed_gallery.image_tags_source_view	=	Ember.View.create({
	tagName:			'tbody',
	templateName:		'image_tags_source_view',
	fetch_image_tags:	function(collection, chosen_ddl){
		collection.clear();
		NggDisplayTab.fetch_in_chunks(
			{action:	'get_image_tags'},
			'image_tags',
			collection,
			25,
			0,
			function(item){
				var arr = this.get('displayed_gallery').get('containers').map(function(obj, index, arr){
					return (item.get('id') == obj.get('id')) ? item : obj
				});
				this.get('displayed_gallery').set('containers', arr);

				return item;
			},
			function(){
				return this.get('source_id') == 'image_tags';
			},
			function(){
				chosen_ddl.update();
			}
		);
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