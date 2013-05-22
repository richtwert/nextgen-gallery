jQuery(function($){
   // Re-arrange menu items
   var overview    = $('a[href="admin.php?page=nextgen-gallery"].wp-first-item').parent();
   var add_gallery = $('a[href*="admin.php?page=ngg_addgallery"]').parent().detach();
   add_gallery.insertAfter(overview);
});
