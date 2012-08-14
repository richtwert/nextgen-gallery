$j = jQuery.noConflict();
$j(function(){
    
   // When an accordion tab is clicked, adjust the form action
   // to include the tab in the querystring so that when the
   // form is submitted, it can use the 'tab' variable to determine
   // which tab should be re-opened.
   $j('.accordion a').click(function(){
      var action = document.createElement('a');
      action.href = window.location.href;
      action.protocol = window.location.protocol;
      action.search = window.location.search;
      action.hash = window.location.hash;
      if (!action.search.match(/tab=/)) {
        action.search = action.search + '&tab=' + this.hash.replace('#', '');
        $j(this).parent().next('.accordion_tab').children('.photocrati_form').attr('action', action);
      }
   }); 
   
   // Activate accordions
   $j('.accordion').accordion({
       active: false,
       autoHeight: false
   });
   
   
   // Uses the 'tab' variable in the query string to automagically display
   // the tab that the user was last visiting
   var match = false;
   if ((match = location.search.match(/tab=(.*)(&)?/))) {
       $j('.accordion a[href="#'+match[1]+'"]').click();
   }
   
   // Activate tooltips
   $j('.tooltip').parent('label').css('cursor', 'help');
   $j('.tooltip').tipTip();
   
   
   // Activate color pickers
   $j('.pick-color').ColorPicker({
       onChange: function(cal, hex) {
           hex = '#'+hex;
           var input = $j(this.data('colorpicker').el);
           input.val(hex).css('background-color', hex);
       },
       onSubmit: function() {
           $j(this.selector).parent().hide();
       },
       onBeforeShow: function(colorpicker){
           $j(this).ColorPickerSetColor($j(this).val());
       }
   }).each(function(){
       $j(this).css('background-color', this.value);
   });
});