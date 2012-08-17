<?php

/***
 {
	Module: photocrati-datamapper,
	Depends: { photocrati-validation }
 }
***/
class M_DataMapper extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-datamapper',
			'DataMapper',
			'Provides a database abstraction layer following the DataMapper pattern',
			'0.1',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Component_Factory', 'A_DataMapper_Factory');

	}


	function _register_hooks()
	{
		add_filter('posts_request', array(&$this, 'set_custom_wp_query'), 50, 2);
		add_filter('posts_fields', array(&$this, 'set_custom_wp_query_fields'), 50, 2);
		add_filter('posts_where', array(&$this, 'set_custom_wp_query_where'), 50, 2);
	}

	/**
	 * Sets a custom SQL query for the WP_Query class, when the Custom Post
	 * DataMapper implementation is used
	 * @param string $sql
	 * @param WP_Query $wp_query
	 * @return string
	 */
	function set_custom_wp_query($sql, &$wp_query)
	{
		$custom_sql = $wp_query->get('custom_sql');
		if ($custom_sql) $sql = $custom_sql;
		return $sql;
	}

	/**
	 * Sets custom fields to select from the database
	 * @param string $fields
	 * @param WP_Query $wp_query
	 * @return string
	 */
	function set_custom_wp_query_fields($fields, &$wp_query)
	{
		$custom_fields = $wp_query->get('fields');
		return $custom_fields ? $custom_fields : $fields;
	}


	/**
	 * Sets custom where clauses for a query
	 * @param string $where
	 * @param WP_Query $wp_query
	 * @return string
	 */
	function set_custom_wp_query_where($where, &$wp_query)
	{
		$this->add_post_title_where_clauses($where, $wp_query);
		$this->add_post_name_where_clauses($where, $wp_query);
		return $where;
	}


	/**
	 * Formats the value of used in a WHERE IN
	 * SQL clause for use in the WP_Query where clause
	 * @param string|array $values
	 * @return string
	 */
	function format_where_in_value($values)
	{
		if (is_string($values) && strpos($values, ',') !== FALSE)
			$values = explode(", ", $values);
		elseif (!is_array($values))
			$values = array($values);

		// Quote the titles
		foreach ($values as $index => $value) {
			$values[$index] = "'{$value}'";
		}

		return implode(', ', $values);
	}


	/**
	 * Adds post_title to the where clause
	 * @param string $where
	 * @param WP_Query $wp_query
	 * @return string
	 */
	function add_post_title_where_clauses(&$where, &$wp_query)
	{
		global $wpdb;

		// Handle post_title query var
		if (($titles = $wp_query->get('post_title'))) {
			$titles = $this->format_where_in_value($titles);
			$where .= " AND {$wpdb->posts}.post_title IN ({$titles})";
		}

		// Handle post_title_like query var
		elseif (($value = $wp_query->get('post_title__like'))) {
			$where .= " AND {$wpdb->posts}.post_title LIKE '{$value}'";
		}
	}


	/**
	 * Adds post_name to the where clause
	 * @param type $where
	 * @param type $wp_query
	 */
	function add_post_name_where_clauses(&$where, &$wp_query)
	{
		global $wpdb;

		if (($name = $wp_query->get('page_name__like'))) {
			$where .= " AND {$wpdb->posts}.post_name LIKE '{$name}'";
		}
		elseif (($names = $wp_query->get('page_name__in'))) {
			$names = $this->format_where_in_value($names);
			$where .= " AND {$wpdb->posts}.post_name IN ({$names})";
		}
	}
}
new M_DataMapper();
?>
