<?php

// Most use site_url() as a base url. But, site_url()
// doesn't always reflect what the user has requested using the browser.
// For example, the site_url() might be 'example.com', but the user typed
// 'www.example.com' instead. We need to match whatever the user used
// otherwise we'll get DOM permission errors
function real_site_url($path="", $scheme=NULL, $admin_url=FALSE)
{
    $site_url = $admin_url? admin_url($path,$scheme) : site_url($path,$scheme);

    if (preg_match("/http(s)?:\/\/([^\/]*)/", $site_url, $match)) {
        $user_domain = $_SERVER['SERVER_NAME'];
        $site_domain = $match[2];
        $site_url = str_replace($site_domain, $user_domain, $site_url);
    }

    return $site_url;
}

// Most use admin_url() as a base url. But, admin_url()
// doesn't always reflect what the user has requested using the browser.
// For example, the site_url() might be 'example.com', but the user typed
// 'www.example.com' instead. We need to match whatever the user used
// otherwise we'll get DOM permission errors
function real_admin_url($path="", $scheme=NULL)
{
    return real_site_url($path, $scheme, TRUE);
}


// Returns TRUE if we're viewing the frontend of the site
function is_frontend()
{
    return !is_backend();
}


// Returns TRUE if we're viewing the backend (admin) of the site
function is_backend()
{
    return strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== FALSE;
}