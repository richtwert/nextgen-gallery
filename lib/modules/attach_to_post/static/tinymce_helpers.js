jQuery(function($){
   
   // Provide a function to close all tinymce windows
   window.close_tinymce_windows = function() {
       for (var key in tinyMCE.activeEditor.windowManager.windows) {
           tinyMCE.activeEditor.windowManager.close(key);
       }
       tinyMCE.activeEditor.contentDocument.activeElement.setAttribute('contenteditable', 'true');
   }
   
   // Append an attached gallery to the TinyMCE editor
   window.append_attached_gallery = function(attached_gallery_id, title) {
       
       var htmlentities = function(value) {
         return $('<div/>').text(value).html().replace(/'/g, "&apos;").replace(/"/g, "&quot;");  
       };
       
       var title = htmlentities(title);
       
       var snippet = "<img class='nggallery_stub' src='"+vars.preview_url+"?attached_gallery_id="+
           attached_gallery_id+"' title='"+title+"' alt='"+title+"'/>";
       
       // Add the snippet if the gallery hasn't already been attached
       if (tinyMCE.activeEditor.startContent.indexOf(snippet) != 0)
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, snippet);
   }
});