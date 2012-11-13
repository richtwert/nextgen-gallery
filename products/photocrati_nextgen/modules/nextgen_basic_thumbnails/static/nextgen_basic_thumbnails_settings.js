jQuery(function($) {
	$('.ngg_thumbnail_override_thumbnail_settings').change(function () {
		var jthis = $(this);

  	if (jthis.val() == '1') {
      var rows = $("tr.nextgen-basic-thumbnails-thumbnail-settings").detach();
      rows.show('slow');
      rows.insertAfter(jthis.parents('tr'));
  	}
    else {
      var rows = $("tr.nextgen-basic-thumbnails-thumbnail-settings").detach();
      rows.insertAfter(jthis.parents('tr'));
      rows.hide('slow');
    }
	});
});

