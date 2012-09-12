<?php

/**
 * Provides rendering logic for the NextGen Basic ImageBrowser
 */
class A_NextGen_Basic_ImageBrowser_Controller extends Mixin
{

    function initialize()
    {
        $this->add_mixin('Mixin_NextGen_Basic_Templates');
    }

	function index_action($displayed_gallery, $return=FALSE)
	{
		$picturelist = array();

		foreach ($displayed_gallery->get_images() as $image) {
			$picturelist[$image->{$image->id_field}] = $image;
		}

		if ($picturelist)
        {
            $retval = $this->Render_Image_Browser(
                $picturelist,
                $displayed_gallery->display_settings['template']
			);

			if ($return)
            {
                return $retval;
            }
			else {
                echo $retval;
            }
		}
		else {
			return $this->object->render_partial('no_images_found', array(), $return);
        }

	}

    /**
     * nggCreateImageBrowser()
     *
     * @access internal
     * @param array $picturelist
     * @param string $template (optional) name for a template file, look for imagebrowser-$template
     * @return the content
     */
    function Render_Image_Browser($picturelist, $template = '')
    {
        global $nggRewrite;

        $settings = $this->object->get_registry()->get_utility('I_NextGen_Settings');

        $pid = get_query_var('pid');
        $current_page = (get_the_ID() == false) ? 0 : get_the_ID();

        // create a array with id's for better walk inside
        foreach ($picturelist as $picture) {
            $picarray[] = $picture->pid;
        }

        $total = count($picarray);

        if (!empty($pid))
        {
            if (is_numeric($pid))
            {
                $act_pid = intval($pid);
            }
            else {
                // in the case it's a slug we need to search for the pid
                foreach ($picturelist as $key => $picture) {
                    if ($picture->image_slug == $pid)
                    {
                        $act_pid = $key;
                        break;
                    }
                }
            }
        }
        else {
            reset($picarray);
            $act_pid = current($picarray);
        }

        // get ids for back/next
        $key = array_search($act_pid, $picarray);
        if (!$key)
        {
            $act_pid = reset($picarray);
            $key = key($picarray);
        }

        $back_pid = ($key >= 1) ? $picarray[$key-1] : end($picarray);
        $next_pid = ($key < ($total - 1)) ? $picarray[$key + 1] : reset($picarray);

        // get the picture data
        $picture = nggdb::find_image($act_pid);

        // if we didn't get some data, exit now
        if (is_null($picture))
        {
            return;
        }

        // add more variables for render output
        $picture->href_link = $picture->get_href_link();
        $args['pid'] = ($settings->usePermalinks) ? $picturelist[$back_pid]->image_slug : $back_pid;
        $picture->previous_image_link = $nggRewrite->get_permalink($args);
        $picture->previous_pid = $back_pid;
        $args['pid'] = ($settings->usePermalinks) ? $picturelist[$next_pid]->image_slug : $next_pid;
        $picture->next_image_link  = $nggRewrite->get_permalink($args);
        $picture->next_pid = $next_pid;
        $picture->number = $key + 1;
        $picture->total = $total;
        $picture->linktitle = (empty($picture->description)) ? ' ' : htmlspecialchars(stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')));
        $picture->alttext = (empty($picture->alttext)) ?  ' ' : html_entity_decode(stripslashes(nggGallery::i18n($picture->alttext, 'pic_' . $picture->pid . '_alttext')));
        $picture->description = (empty($picture->description)) ? ' ' : html_entity_decode(stripslashes(nggGallery::i18n($picture->description, 'pic_' . $picture->pid . '_description')));
        $picture->anchor = 'ngg-imagebrowser-' . $picture->galleryid . '-' . $current_page;

        // filter to add custom content for the output
        $picture = apply_filters('ngg_image_object', $picture, $act_pid);

        // let's get the meta data
        $meta = new nggMeta($act_pid);
        $meta->sanitize();
        $exif = $meta->get_EXIF();
        $iptc = $meta->get_IPTC();
        $xmp  = $meta->get_XMP();
        $db   = $meta->get_saved_meta();

        //if we get no exif information we try the database
        $exif = ($exif == false) ? $db : $exif;

        // look for imagebrowser-$template.php or pure imagebrowser.php
        $filename = (empty($template)) ? 'imagebrowser' : 'imagebrowser-' . $template;

        // create the output
        $out = $this->object->legacy_render(
            $filename,
            array(
                'image' => $picture,
                'meta' => $meta,
                'exif' => $exif,
                'iptc' => $iptc,
                'xmp' => $xmp,
                'db' => $db
            )
        );

        return $out;
    }

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array('nextgen_basic_templates_template');
    }

}
