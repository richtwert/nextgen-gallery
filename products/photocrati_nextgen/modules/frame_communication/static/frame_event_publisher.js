window.Frame_Event_Publisher = {
	cookie_name: 'frame_events',
	received: [],
	initialized: false,
	children: [],

	is_parent: function(){
		return self.parent.document === self.document;
	},

	is_child: function(){
		return !this.is_parent();
	},

	initialize: function(){
		this.received = this.get_events(document.cookie);
		this.initialized = true;
		if (this.is_parent()) this.emit(this.received, true);
		return this.received;
	},

	register_child: function(child) {
		if (this.children.indexOf(child) < 0) this.children.push(child);
	},

	broadcast: function(events, child){
		if (!this.initialized) events = this.initialize();
		if (this.is_child()) {
			if (arguments.length <= 1) child = window;
			this.find_parent(child).register_child(child.Frame_Event_Publisher);
			this.notify_parent(events, child);
		}
		else {
			if (arguments.length == 0) events = this.received;
			this.notify_children(events);
		}

	},

	/**
	 * Notifies the parent with a list of events to broadcast
	 */
	notify_parent: function(events, child){
		this.find_parent(child).broadcast(events, child);
	},

	/**
	 * Notifies (broadcasts) to children the list of available events
	 */
	notify_children: function(events){
		this.emit(events);
		for (var index in this.children) {
			var child = this.children[index];
			child.emit(events);
		}
	},

	/**
	 * Finds the parent window for the current child window
	 */
	find_parent: function(child){
		var retval = child;
		try {
			while (retval.document !== retval.parent.document) retval = retval.parent;
		}
		catch (Exception){
		}
		return retval.Frame_Event_Publisher;
	},

	/**
	 * Emits all known events to all children
	 */
	emit: function(events, forced){
		for (var context in events) {
			for (var event_id in events[context]) {
				var event = events[context][event_id];
				if (forced || !this.has_received_event(context, event_id)) {
					this.trigger_event(context, event_id, event);
				}
			}
		}
	},

	has_received_event: function(context, id){
		var retval = true;
		if (this.received[context] == undefined) retval = false;
		else if (this.received[context][id] == undefined) retval = false;
		return retval;
	},

	trigger_event: function(context, id, event){
		var signal = context+':'+event.event;
		if (this.received[context] == undefined) this.received[context] = {};
		this.received[context][id] = event;
		jQuery(window).trigger(signal, event);

	},

	/**
	 * Parses the events found in the cookie
	 */
	get_events: function(cookie){
		var frame_events = JSON.parse(unescape(cookie.match(/frame_events=([^ ]*)/).pop().slice(0,-1)));
		return frame_events;
	}
}

jQuery(function($){
	$(window).bind('attach_to_post:test', function(e, data){
		console.log(data);
	});
	Frame_Event_Publisher.broadcast();
});

