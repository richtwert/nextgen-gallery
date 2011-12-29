<?php

function h($str)
{
    return str_replace("'", "&#39;", htmlentities($str));
}

function echo_h($str)
{
    echo h($str);
}