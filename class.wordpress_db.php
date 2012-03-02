<?php

class Mixin_WordPress_Db extends Mixin
{    
    /**
     * If the column 'id' is detected in the string, it substitutes it with
     * the value of the object's id_field property
     * @param string $sql_query 
     */
    function _substitute_id_for_id_field($sql_query, $id_field)
    {
        return str_replace('  ', ' ', (str_replace(' id', ' '.$id_field, ' '.$sql_query)));
    }
    
    /**
     * Returns the name of a table
     * @param string table
     * @return string 
     */
    function get_table_name($table)
    {
        switch($table) {
            case 'galleries':
            case 'nggalleries':
            case 'pc_galleries':
            case 'nggallery':
            case 'pc_gallery':
            case 'pcgallery':
                $table = $this->object->get_wrapped_class()->nggallery;
                break;
            case 'gallery_image':
            case 'pc_gallery_image':
            case 'nggpictures':
            case 'nggpicture':
            case 'ngg_picture':
            case 'ngg_pictures':
                $table = $this->object->get_wrapped_class()->nggpictures;
                break;
            case 'postmeta':
            case 'post_meta':
                $table = $this->object->get_wrapped_class()->postmeta;
                break;
            case 'options':
                $table = $this->object->get_wrapped_class()->options;
                break;
        }
        
        return $table;
    }
}


class C_WordPress_Db extends C_Component
{   
    function define()
    {
    		parent::define();
    		
        $this->add_mixin('Mixin_WordPress_Db');
        $this->wrap('wpdb', array($this, 'wrap_wpdb'));
        $this->implement('I_Db');
    }
    
    /**
     * Instructs ExtensibleObject how to instantiate wpdb
     * @global wpdb $wpdb
     * @return wpdb 
     */
    function wrap_wpdb()
    {   
        global $wpdb;
        
        return $wpdb;
    }
}
