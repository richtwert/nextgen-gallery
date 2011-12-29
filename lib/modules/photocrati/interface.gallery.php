<?php

interface I_Gallery
{
    function get_images($start=0, $limit=0, $context=FALSE);
    
    function import_image($image=array());
}