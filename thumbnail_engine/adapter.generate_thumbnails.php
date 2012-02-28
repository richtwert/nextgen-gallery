<?php

/**
 * Adapts an imported image to generate thumbnails after it has been saved
 */
class A_Generate_Thumbnails extends Hook
{
    function initialize()
    {
        $this->object->add_post_hook(
            'save',
            'generate_thumbnails',
            get_class($this), 
            'generate_thumbnails'
        );
    }
    
    /**
     * Based on NextGen's mechanism to generate thumbnails
     */
    function generate_thumbnails()
    {
        // Only generate the thumbnails if the save method was successful
        if ($this->object->has_errors()) return;
        
        // Get thumbnail configuration
        $factory = $this->object->_registry->get_singleton_utility('I_Component_Factory');
        $config = $factory->create('thumbnail_config');
        
        // Create a thumbnail generator
        $generator = $factory->create('thumbnail_Generator', $this->object, $config);
        
        // If a global thumbnail does not exist for this thumbnail yet, then
        // generate one now
        $size = $generator->process(TRUE);
        
        // Clean up
        unset($factory);
        
        // Update metadata
        $metadata = $this->object->properties['meta_data'];
        $metadata = $this->object->try_unserialize($metadata);
        $metadata['thumbnail'] = $size;
        $this->object->properties['meta_data'] = $metadata;
        
        // Should call Active Record's save mechanism.
        $args = func_get_args();
        $this->call_anchor($args);;
    }
}