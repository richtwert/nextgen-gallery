<?php

/**
 * Provides the frontend view for NextGen Thumbnail Galleries 
 */
class C_NextGen_Thumbnail_Gallery_View extends C_Base_Gallery_View_Controller
{      
    function enqueue_scripts()
    {
        $this->resource_loader->enqueue_script(
            'nextgen_thumbnail_gallery'
        );
    }
    

    // Renders the gallery type frontend
    // Much of this code is an extract from nggfunctions.php.
    function index()
    {
        global $nggRewrite;

        // Don't display notices, as legacy template does not properly
        // check for variable declaration
        $er = error_reporting(error_reporting() ^ E_NOTICE);

        // Get global options and merge with gallery instance options
        $ngg_options = $this->array_merge_assoc(
            nggGallery::get_option('ngg_options'),
            $this->gallery_instance_settings_to_ngg_legacy(
                $this->gallery_instance->settings
            )
        );

        // $_GET from wp_query
        $show    = get_query_var('show');

        // go on only on this page
        if ( !is_home() || $pageid == get_the_ID() ) { 

            // 2nd look for slideshow
            if ( $show == 'slide' ) {
                $args['show'] = "gallery";
                $out  = '<div class="ngg-galleryoverview">';
                if ($this->gallery_instance->settings['show_thumbnails_link']) {
                    $out .= '<div class="slideshowlink"><a class="slideshowlink" href="' . $nggRewrite->get_permalink($args) . '">'.nggGallery::i18n($ngg_options['galTextGallery']).'</a></div>';
                }
                $out .= nggShowSlideshow($this->gallery_instance->gallery_id, $ngg_options['irWidth'], $ngg_options['irHeight']);
                $out .= '</div>'."\n";
                $out .= '<div class="ngg-clear"></div>'."\n";
                echo $out;
                return;
            }
        }
            
        // Call NextGen legacy methods
        echo $this->nggCreateGallery(
            $this->gallery_instance->get_images(TRUE),
            $this->gallery_instance->gallery_id,
            $this->gallery_instance->display_template,
            FALSE,
            $this->gallery_instance_settings_to_ngg_legacy(
                $this->gallery_instance->settings
            )
        ); 
        
        // Restore original error reporting setting
        error_reporting($er);
    }
    
    /**
     * Convert the gallery instance settings to the names of legacy settings
     * @param array $settings
     * @return array 
     */
    function gallery_instance_settings_to_ngg_legacy($settings)
    {
        return array(
            'galShowSlide'      =>  $settings['show_slideshow_link'],
            'galTextSlide'      =>  $settings['slideshow_link_text'],
            'galColumns'        =>  $settings['num_of_columns'],
            'usePicLens'        =>  $settings['show_piclens_link'],
            'galImages'         =>  $settings['images_per_page'],
            'galTextGallery'    =>  $settings['thumbnail_link_text'],
            'piclens_link_text' =>  $settings['piclens_link_text']
            
        );
    }
    
    // This code is taken from nggfunctions.php and the nggCreateGallery function.
    // We're duplicating this function as nggCreateGallery provides no way of
    // overriding the default ngg_options, needed by our plugin
    // TODO: If we own NextGen, we need to alter nggCreateGallery to accept
    // a list of options which override the global options
    function nggCreateGallery($picturelist, $galleryID = false, $template = '', $images = false, $overrides=array())
    {
        global $nggRewrite;

        require_once (NGGALLERY_ABSPATH . '/lib/media-rss.php');

        $ngg_options = nggGallery::get_option('ngg_options');
        
        // TODO: If we own NextGen, put this block into nggCreateGallery of
        // nggfunctions.php and delete this function
        if ($overrides) {
            $ngg_options = $this->array_merge_assoc($ngg_options, $overrides);
        }

        //the shortcode parameter will override global settings, TODO: rewrite this to a class
        $ngg_options['galImages'] = ( $images === false ) ? $ngg_options['galImages'] : (int) $images;  

        $current_pid = false;

        // $_GET from wp_query
        $nggpage  = get_query_var('nggpage');
        $pageid   = get_query_var('pageid');
        $pid      = get_query_var('pid');

        // in case of permalinks the pid is a slug, we need the id
        if( !is_numeric($pid) && !empty($pid) ) {
            $picture = nggdb::find_image($pid);        
            $pid = $picture->pid;
        }   

        // we need to know the current page id
        $current_page = (get_the_ID() == false) ? 0 : get_the_ID();

        if ( !is_array($picturelist) )
            $picturelist = array($picturelist);

        // Populate galleries values from the first image           
        $first_image = current($picturelist);
        $gallery = new stdclass;
        $gallery->ID = (int) $galleryID;
        $gallery->show_slideshow = false;
        $gallery->show_piclens = false;
        $gallery->name = stripslashes ( $first_image->name  );
        $gallery->title = stripslashes( $first_image->title );
        $gallery->description = html_entity_decode(stripslashes( $first_image->galdesc));
        $gallery->pageid = $first_image->pageid;
        $gallery->anchor = 'ngg-gallery-' . $galleryID . '-' . $current_page;
        reset($picturelist);

        $maxElement  = $ngg_options['galImages'];
        $thumbwidth  = $ngg_options['thumbwidth'];
        $thumbheight = $ngg_options['thumbheight'];     

        // fixed width if needed
        $gallery->columns    = intval($ngg_options['galColumns']);
        $gallery->imagewidth = ($gallery->columns > 0) ? 'style="width:' . floor(100/$gallery->columns) . '%;"' : '';

        // obsolete in V1.4.0, but kept for compat reason
            // pre set thumbnail size, from the option, later we look for meta data. 
        $thumbsize = ($ngg_options['thumbfix']) ? $thumbsize = 'width="' . $thumbwidth . '" height="'.$thumbheight . '"' : '';

        // show slideshow link
        if ($galleryID) {
            if ($ngg_options['galShowSlide']) {
                $gallery->show_slideshow = true;
                $gallery->slideshow_link = $nggRewrite->get_permalink(array ( 'show' => 'slide') );
                $gallery->slideshow_link_text = nggGallery::i18n($ngg_options['galTextSlide']);
            }

            if ($ngg_options['usePicLens']) {
                $gallery->show_piclens = true;
                $gallery->piclens_link = "javascript:PicLensLite.start({feedUrl:'" . htmlspecialchars( nggMediaRss::get_gallery_mrss_url($gallery->ID) ) . "'});";                
            }
        }

        // check for page navigation
        if ($maxElement > 0) {

            if ( !is_home() || $pageid == $current_page )
                $page = ( !empty( $nggpage ) ) ? (int) $nggpage : 1;
            else 
                $page = 1;

            $start = $offset = ( $page - 1 ) * $maxElement;

            $total = count($picturelist);

                    //we can work with display:hidden for some javascript effects
            if (!$ngg_options['galHiddenImg']){
                    // remove the element if we didn't start at the beginning
                    if ($start > 0 ) 
                        array_splice($picturelist, 0, $start);

                    // return the list of images we need
                    array_splice($picturelist, $maxElement);
            }

            $nggNav = new nggNavigation;    
            $navigation = $nggNav->create_navigation($page, $total, $maxElement);
        } else {
            $navigation = '<div class="ngg-clear"></div>';
        } 

        //we cannot use the key as index, cause it's filled with the pid
            $index = 0;
        foreach ($picturelist as $key => $picture) {

                    //needed for hidden images (THX to Sweigold for the main idea at : http://wordpress.org/support/topic/228743/ )
                    $picturelist[$key]->hidden = false;	
                    $picturelist[$key]->style  = $gallery->imagewidth;

                    if ($maxElement > 0 && $ngg_options['galHiddenImg']) {
                            if ( ($index < $start) || ($index > ($start + $maxElement -1)) ){
                                    $picturelist[$key]->hidden = true;	
                                    $picturelist[$key]->style  = ($gallery->columns > 0) ? 'style="width:' . floor(100/$gallery->columns) . '%;display: none;"' : 'style="display: none;"';
                            }
                            $index++;
                    }

            // get the effect code
            if ($galleryID)
                $thumbcode = ($ngg_options['galImgBrowser']) ? '' : $picture->get_thumbcode('set_' . $galleryID);
            else
                $thumbcode = ($ngg_options['galImgBrowser']) ? '' : $picture->get_thumbcode(get_the_title());

            // create link for imagebrowser and other effects
            $args ['nggpage'] = empty($nggpage) ? false : $nggpage;
            $args ['pid']     = ($ngg_options['usePermalinks']) ? $picture->image_slug : $picture->pid;
            $picturelist[$key]->pidlink = $nggRewrite->get_permalink( $args );

            // generate the thumbnail size if the meta data available
            if (is_array ($size = $picturelist[$key]->meta_data['thumbnail']) )
                    $thumbsize = 'width="' . $size['width'] . '" height="' . $size['height'] . '"';

            // choose link between imagebrowser or effect
            $link = ($ngg_options['galImgBrowser']) ? $picturelist[$key]->pidlink : $picture->imageURL; 
            // bad solution : for now we need the url always for the carousel, should be reworked in the future
            $picturelist[$key]->url = $picture->imageURL;
            // add a filter for the link
            $picturelist[$key]->imageURL = apply_filters('ngg_create_gallery_link', $link, $picture);
            $picturelist[$key]->thumbnailURL = $picture->thumbURL;
            $picturelist[$key]->size = $thumbsize;
            $picturelist[$key]->thumbcode = $thumbcode;
            $picturelist[$key]->caption = ( empty($picture->description) ) ? '&nbsp;' : html_entity_decode ( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
            $picturelist[$key]->description = ( empty($picture->description) ) ? ' ' : htmlspecialchars ( stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')) );
            $picturelist[$key]->alttext = ( empty($picture->alttext) ) ?  ' ' : htmlspecialchars ( stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')) );

            // filter to add custom content for the output
            $picturelist[$key] = apply_filters('ngg_image_object', $picturelist[$key], $picture->pid);

            //check if $pid is in the array
            if ($picture->pid == $pid) 
                $current_pid = $picturelist[$key];
        }
        reset($picturelist);

        //for paged galleries, take the first image in the array if it's not in the list
        $current_pid = ( empty($current_pid) ) ? current( $picturelist ) : $current_pid;

        // look for gallery-$template.php or pure gallery.php
        $filename = ( empty($template) ) ? 'gallery' : 'gallery-' . $template;

        //filter functions for custom addons
        $gallery     = apply_filters( 'ngg_gallery_object', $gallery, $galleryID );
        $picturelist = apply_filters( 'ngg_picturelist_object', $picturelist, $galleryID );

        //additional navigation links
        $next = ( empty($nggNav->next) ) ? false : $nggNav->next;
        $prev = ( empty($nggNav->prev) ) ? false : $nggNav->prev;

        // create the output
        $out = nggGallery::capture ( $filename, array ('gallery' => $gallery, 'images' => $picturelist, 'pagination' => $navigation, 'current' => $current_pid, 'next' => $next, 'prev' => $prev) );
        
        // Substitute NextGen's PicLens label with the user-specified option
        $out = str_replace("[View with PicLens]", $ngg_options['piclens_link_text'], $out);

        // apply a filter after the output
        $out = apply_filters('ngg_gallery_output', $out, $picturelist);

        return $out;
    }
}