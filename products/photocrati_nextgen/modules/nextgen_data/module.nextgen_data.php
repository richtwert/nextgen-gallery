<?php

/***
{
		Module: photocrati-nextgen-data,
		Depends: { photocrati-nextgen_settings, photocrati-datamapper }
}
***/

class M_NextGen_Data extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen-data',
            'NextGEN Data Tier',
            "Provides a data tier for NextGEN gallery based on the DataMapper module",
            '0.1',
            'http://www.photocrati.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_NextGen_Data_Factory');
		$this->get_registry()->add_adapter('I_CustomPost_DataMapper', 'A_Attachment_DataMapper', 'attachment');
		$this->get_registry()->add_adapter('I_CustomTable_DataMapper', 'A_CustomTable_Sorting_DataMapper');
        $this->get_registry()->add_adapter('I_Installer', 'A_NextGen_Data_Installer');
    }


    function _register_utilities()
    {
		$this->get_registry()->add_utility('I_Gallery_Mapper', 'C_Gallery_Mapper');
		$this->get_registry()->add_utility('I_Image_Mapper', 'C_Image_Mapper');
        $this->get_registry()->add_utility('I_Album_Mapper', 'C_Album_Mapper');
        $this->get_registry()->add_utility('I_Transients', 'C_NextGen_Transients');
        $this->get_registry()->add_utility('I_Gallery_Storage', 'C_Gallery_Storage');
    }

    function set_file_list()
    {
        return array(
            'adapter.attachment_datamapper.php',
            'adapter.customtable_sorting_datamapper.php',
            'adapter.nextgen_data_factory.php',
            'adapter.nextgen_data_installer.php',
            'adapter.parse_image_metadata.php',
            'class.album.php',
            'class.gallery.php',
            'class.image.php',
            'class.album_mapper.php',
            'class.gallerystorage_base.php',
            'class.gallerystorage_driver_base.php',
            'class.gallery_mapper.php',
            'class.gallery_storage.php',
            'class.image_mapper.php',
            'class.image_wrapper.php',
            'class.image_wrapper_collection.php',
            'class.nextgen_metadata.php',
            'class.nextgen_transients.php',
            'class.ngglegacy_gallerystorage_driver.php',
            'class.ngglegacy_thumbnail.php',
            'class.wordpress_gallerystorage_driver.php',
            'interface.album.php',
            'interface.gallery.php',
            'interface.image.php',
            'interface.transients.php',
            'interface.album_mapper.php',
            'interface.component_config.php',
            'interface.gallerystorage_driver.php',
            'interface.gallery_mapper.php',
            'interface.gallery_storage.php',
            'interface.gallery_type.php',
            'interface.image_mapper.php'
        );
    }
    
    
    function _register_hooks()
    {
    	add_filter('posts_orderby', array($this, 'wp_query_order_by'), 10, 2);
    }
    
    function wp_query_order_by($order_by, $wp_query)
    {
    	if ($wp_query->get('datamapper_attachment'))
    	{
    		$order_parts = explode(' ', $order_by);
    		$order_name = array_shift($order_parts);
    		
    		$order_by = 'ABS(' . $order_name . ') ' . implode(' ', $order_parts) . ', ' . $order_by;
    	}
    	
    	return $order_by;
    }
}
new M_NextGen_Data();
