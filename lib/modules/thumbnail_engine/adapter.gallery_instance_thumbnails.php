<?php

class A_Gallery_Instance_Thumbnails extends Mixin
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save',
            'generate_thumbnails',
            get_class(),
            'generate_thumbnails'    
        );
    }
    
    
    function generate_thumbnails()
    {   
        // Was the save method successful?
        $success = $this->object->get_method_property(
            'save',
            ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
        );
        
        // Does this gallery instance require thumbnails?
        if ($success && $this->object->settings && isset($this->object->settings['generate_thumbnails'])) {
            
            // Instantiate the factory. We're gonna need it.
            $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
            
            // Iterate through each image of the instance and generate a thumbnail for it
            foreach ($this->object->image as $id => $props) {
                $image = $factory->create('gallery_image');
                $image = $image->find($id);
                $image->update_properties($props);
                
                // Instantiate thumbnail generator
                $generator = $factory->create('thumbnail_generator', $image, (object)$this->object->settings);
                $generator->process();
            }
        }
    }
}