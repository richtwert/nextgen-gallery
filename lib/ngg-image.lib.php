<?php

/**
* Image PHP class for the WordPress plugin NextGEN Gallery
* 
* @author 		Alex Rabe 
* @copyright 	Copyright 2007-2008
*/
class nggImage{
	
	/**** Public variables ****/	
	var $errmsg			=	"";			// Error message to display, if any
	var $error			=	FALSE; 		// Error state
	var $imageURL		=	"";			// URL Path to the image
	var $thumbURL		=	"";			// URL Path to the thumbnail
	var $imagePath		=	"";			// Server Path to the image
	var $thumbPath		=	"";			// Server Path to the thumbnail
	var $href			=	"";			// A href link code
	
	// TODO: remove thumbPrefix and thumbFolder (constants)
	var $thumbPrefix	=	"";			// FolderPrefix to the thumbnail
	var $thumbFolder	=	"";			// Foldername to the thumbnail
	
	/**** Image Data ****/
	var $galleryid		=	0;			// Gallery ID
	var $pid			=	0;			// Image ID	
	var $filename		=	"";			// Image filename
	var $description	=	"";			// Image description	
	var $alttext		=	"";			// Image alttext	
	var $exclude		=	"";			// Image exclude
	var $thumbcode		=	"";			// Image effect code

	/**** Gallery Data ****/
	var $gallery 		= 	null;		// TODO: remove the fields below
	var $name			=	"";			// Gallery name
	var $path			=	"";			// Gallery path	
	var $title			=	"";			// Gallery title
	var $pageid			=	0;			// Gallery page ID
	var $previewpic		=	0;			// Gallery preview pic		

	var $permalink		=	'';
	var $tags			=   '';
		
	/**
	* Constructor
	* 
	* @gallery The nggGallery object representing the gallery containing this image
	* @row The database row from which to initialise the object fields
	*/
	function nggImage($gallery, $row) {			
		// Copy fields from database row
		//--
		foreach ($row as $key => $value) {
			$this->$key = $value ;
		}
		
		// Finish initialisation
		//--
		$this->gallery 		= $gallery;
		$this->name			= $gallery->name;
		$this->path			= $gallery->path;
		$this->title		= $gallery->title;
		$this->pageid		= $gallery->pageid;		
		$this->previewpic	= $gallery->previewpic;
	
		// set urls and paths
		//--
		$this->get_thumbnail_folder($this->gallery->path, FALSE);
		$this->imageURL		= get_option ('siteurl') . "/" . $this->path . "/" . $this->filename;
		$this->thumbURL 	= get_option ('siteurl') . "/" . $this->path . $this->thumbFolder . $this->thumbPrefix . $this->filename;
		$this->imagePath	= WINABSPATH.$this->path . "/" . $this->filename;
		$this->thumbPath	= WINABSPATH.$this->path . "/" . $this->thumbFolder . $this->thumbPrefix . $this->filename;
		
		// Get tags only if necessary
		//--
		unset($this->tags);
	}
	
	/**********************************************************/
	function get_thumbnail_folder($gallerypath, $include_Abspath = TRUE) {
		//TODO:Double coded, see also class nggallery, fix it !
		if (!$include_Abspath) 
			$gallerypath = WINABSPATH.$gallerypath;
		
		if (!file_exists($gallerypath))
			return FALSE;
		
		if (is_dir($gallerypath."/thumbs")) {
			$this->thumbFolder 	= "/thumbs/";
			$this->thumbPrefix 	= "thumbs_";		
			return TRUE;
		}
		
		if (is_admin()) {
			if (!is_dir($gallerypath."/thumbs")) {
				if ( !wp_mkdir_p($gallerypath."/thumbs") ) {
					return FALSE;
				}
				$this->thumbFolder	= "/thumbs/";
				$this->thumbPrefix 	= "thumbs_";			
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Get the thumbnail code (to add effects on thumbnail click)
	*
	* Applies the filter ''
	*/
	function get_thumbcode($galleryname = "") {
		// read the option setting
		$ngg_options = get_option('ngg_options');
		
		// get the effect code
		if ($ngg_options['thumbEffect'] != "none")
			$this->thumbcode = stripslashes($ngg_options['thumbCode']);		
		
		// for highslide to a different approach	
		if ($ngg_options['thumbEffect'] == "highslide")
			$this->thumbcode = str_replace("%GALLERY_NAME%", "'".$galleryname."'", $this->thumbcode);
		else
			$this->thumbcode = str_replace("%GALLERY_NAME%", $galleryname, $this->thumbcode);
				
		return apply_filters('ngg_get_thumbcode', $this->thumbcode, $this);
	}
	
	function get_href_link() {
		// create the a href link from the picture
		$this->href  = "\n".'<a href="'.$this->imageURL.'" title="'.stripslashes($this->description).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->imageURL.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}

	function get_href_thumb_link() {
		// create the a href link with the thumbanil
		$this->href  = "\n".'<a href="'.$this->imageURL.'" title="'.stripslashes($this->description).'" '.$this->get_thumbcode($this->name).'>'."\n\t";
		$this->href .= '<img alt="'.$this->alttext.'" src="'.$this->thumbURL.'"/>'."\n".'</a>'."\n";

		return $this->href;
	}
	
	function cached_singlepic_file($width = "", $height = "", $mode = "" ) {
		// This function creates a cache for all singlepics to reduce the CPU load
		$ngg_options = get_option('ngg_options');
		
		include_once( nggGalleryPlugin::graphic_library() );
		
		// cache filename should be unique
		$cachename   	= $this->pid . "_" . $mode . "_". $width . "x" . $height . "_" . $this->filename;
		$cachefolder 	= WINABSPATH .$ngg_options['gallerypath'] . "cache/";
		$cached_url  	= get_option ('siteurl') ."/". $ngg_options['gallerypath'] . "cache/" . $cachename;
		$cached_file	= $cachefolder . $cachename;
		
		// check first for the file
		if ( file_exists($cached_file) ) {
			return $cached_url;
		}
		
		// create folder if needed
		if ( !file_exists($cachefolder) ) {
			if ( !wp_mkdir_p($cachefolder) ) {
				return false;
			}
		}
		
		$thumb = new ngg_Thumbnail($this->imagePath, TRUE);
		// echo $thumb->errmsg;
		
		if (!$thumb->error) {	
			$thumb->resize($width , $height);
			
			if ($mode == 'watermark') {
				if ($ngg_options['wmType'] == 'image') {
					$thumb->watermarkImgPath = $ngg_options['wmPath'];
					$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']); 
				}
				if ($ngg_options['wmType'] == 'text') {
					$thumb->watermarkText = $ngg_options['wmText'];
					$thumb->watermarkCreateText($ngg_options['wmColor'], $ngg_options['wmFont'], $ngg_options['wmSize'], $ngg_options['wmOpaque']);
					$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']);  
				}
			}
			
			if ($mode == 'web20') {
				$thumb->createReflection(40,40,50,false,'#a4a4a4');
			}
			
			// save the new cache picture
			$thumb->save($cached_file,$ngg_options['imgQuality']);
		}
		$thumb->destruct();
		
		// check again for the file
		if (file_exists($cached_file)) {
			return $cached_url;
		}
		
		return false;
	}
	
	/**
	 * Get the tags associated to this image
	 */
	function get_tags() {
		if (!isset($this->tags)) {
			$this->tags = wp_get_object_terms($this->pid, 'ngg_tag', 'fields=all');
		}
		return $this->tags;
	}
	
	/**
	 * Get the permalink to the image
	 * TODO Get a permalink to a page presenting the image
	 */
	function get_permalink() {
		if ($this->permalink=='') {
			$this->permalink = $this->imageURL;
		}
		return $this->permalink; 
	}
}

?>