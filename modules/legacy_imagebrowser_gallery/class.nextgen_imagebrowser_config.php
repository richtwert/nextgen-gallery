<?php

class Mixin_NextGen_ImageBrowser_Config extends Mixin
{
}

class C_NextGen_ImageBrowser_Config extends C_Base_Component_Config
{
   function define()
   {
       parent::define();
       $this->add_mixin('Mixin_NextGen_ImageBrowser_Config');
   }
}