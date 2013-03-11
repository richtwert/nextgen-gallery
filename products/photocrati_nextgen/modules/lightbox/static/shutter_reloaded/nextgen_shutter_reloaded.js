jQuery(function($){
	var callback = function(){
		var shutterLinks = {}, shutterSets = {}; shutterReloaded.Init();
	};
	$(this).bind('refreshed', callback);
	$('ngg-galleryoverview').ready(callback);
});