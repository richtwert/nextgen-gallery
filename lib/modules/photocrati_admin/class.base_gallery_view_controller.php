<?php

class Mixin_Base_Gallery_View_Static_Resources extends Mixin
{   
    function index()
    {
        echo "<p>";
        echo_h(_("No view defined for this gallery type"));
        echo "</p>";
    }
}

/**
 * Renders the frontend view of the gallery 
 */
class C_Base_Gallery_View_Controller extends C_MVC_Controller
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Base_Gallery_View_Static_Resources');
    }
    
    function initialize($context=FALSE)
    {
        parent::initialize();
        $this->resource_loader = $this->_registry->get_utility('I_Resource_Loader');
    }
}