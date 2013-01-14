jQuery(function($){
	$('input[name="settings[jsconsole_enabled]"]').click(function(){
		$('#jsconsole_session_key_row').fadeToggle('slow');
	});
});