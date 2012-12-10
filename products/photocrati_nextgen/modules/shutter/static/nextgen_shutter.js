jQuery(function($){
    var callback = function(){
        var shutterLinks = {}, shutterSets = {}; shutterReloaded.init();
    };
    $(this).bind('refreshed', callback);
    $('ngg-galleryoverview').ready(callback);
});
