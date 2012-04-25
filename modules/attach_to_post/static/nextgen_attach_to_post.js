(function() {
        tinymce.create('tinymce.plugins.NextGen_AttachToPost', {
                /**
                 * Initializes the plugin, this will be executed after the plugin has been created.
                 * This call is done before the editor instance has finished it's initialization so use the onInit event
                 * of the editor instance to intercept that event.
                 *
                 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
                 * @param {string} url Absolute URL to where the plugin is located.
                 */
                init : function(ed, url) {
                
                        // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
                        ed.addCommand('attach_gallery', function(attached_gallery_id) {
                                var post_id = tinymce_nextgen.post_id;
//                                var post_id = window.location.search.match(/post=(\d+)/)[1];
                                var attach_gallery_url = tinymce_nextgen.attach_url + '?post_id=' + post_id;
                                if (attached_gallery_id) attach_gallery_url += "&attached_gallery_id="+attached_gallery_id;
                                ed.windowManager.open({
                                        title: "Attach Gallery",
                                        close_previous: true,
                                        file : attach_gallery_url,
                                        width : 820,
                                        height: 400,
                                        inline : 1
                                });
                        });

                        // Register example button
                        ed.addButton('NextGen_AttachToPost', {
                                title : 'Attach Gallery',
                                cmd : 'attach_gallery',
                                image : 'http://www.mricons.com/store/png/110948_27864_24_gallery_image_landscape_photo_icon.png'
                        });
                        
                        
                        // Fires when an element inside of the tinymce editor is
                        // clicked.
                        //
                        // We look to see if an image has been clicked, as it
                        // might be a placeholder image. Placeholder images
                        // will have the gallery instance id in the url
                        ed.onMouseDown.addToTop(function(ed, e){
                            var attached_gallery_id = e.target.tagName == 'IMG' ? e.target.src.match(/attached_gallery_id=(\d+)/)[1] : false;
                            if (attached_gallery_id) {
                                ed.contentDocument.activeElement.setAttribute('contenteditable', 'false');
                                ed.execCommand('attach_gallery', attached_gallery_id);
                                return false;
                            }
                        });

                        // Add a node change handler, selects the button in the UI when a image is selected
                        ed.onNodeChange.add(function(ed, cm, n) {
                                cm.setActive('NextGen_AttachToPost', n.className == 'nggallery_stub');
                        });
                },

                /**
                 * Returns information about the plugin as a name/value array.
                 * The current keys are longname, author, authorurl, infourl and version.
                 *
                 * @return {Object} Name/value array containing information about the plugin.
                 */
                getInfo : function() {
                        return {
                                longname : 'NextGen - Attach Gallery',
                                author : 'Photocrati Media',
                                authorurl : 'http://tinymce.moxiecode.com',
                                infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/NextGen_AttachToPost',
                                version : "1.0"
                        };
                }
        });

        // Register plugin
        tinymce.PluginManager.add('NextGen_AttachToPost', tinymce.plugins.NextGen_AttachToPost);
})();
