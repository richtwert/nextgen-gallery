<?php

/**
 * Thrown when an entity does not exist
 */
class E_EntityNotFoundException extends RuntimeException
{

}

/**
 * Thrown when an invalid data type is used as an entity, such as an associative
 * array which is not yet supported due to a problem with references and the
 * call_user_func_array() function.
 */
class E_InvalidEntityException extends RuntimeException
{
	function __construct($message=FALSE, $code=0, $previous=NULL)
	{
		if (!$message) {
			$message =  "Invalid data type used for entity. Please use stdClass
				or a subclass of C_DataMapper_Model. Arrays will be supported in
				the future.";
		}

		parent::__construct($message, $code, $previous);
	}
}


class Mixin_DataMapper_Driver_Base extends Mixin
{
	/**
	 * Used to clean column or table names in a SQL query
	 * @param string $val
	 * @return string
	 */
	function _clean_column($val)
	{
		return str_replace(
			array(';', "'", '"', '`'),
			array(''),
			$val
		);
	}


	/**
	 * Finds a partiular entry by id
	 * @param int $id
	 * @return C_DataMapper_Entity
	 */
	function find($id, $model=FALSE)
	{
		$results = $this->object->select()->where_and(
				array("{$this->object->get_primary_key_column()} = %d", $id)
		)->limit(1,0)->run_query();

		if ($results)
			return $model? $this->object->convert_to_model($results[0]) : $results[0];
		else
			return NULL;
	}

	/**
	 * Fetches the first row
	 * @param array $conditions
	 * @return C_DataMapper_Entity
	 */
	function find_first($conditions=array(), $model=FALSE)
	{
		$results = $this->object->select()->where_and($conditions)->limit(1,0)->run_query();
		if ($results)
			return $model? $this->object->convert_to_model($results[0]) : $results[0];
		else
			return NULL;
	}


	/**
	 * Queries all rows
	 * @param array $conditions
	 * @return array
	 */
	function find_all($conditions=array(), $model=FALSE)
	{
		$results = $this->object->select()->where_and($conditions)->run_query();
		if ($results && $model) {
			foreach ($results as &$r) {
				$r = $this->object->convert_to_model($r);
			}
		}
		return $results;
	}


	/**
	 * Filters the query using conditions:
	 * E.g.
	 *		array("post_title = %s", "Foo")
	 *		array(
	 *			array("post_title = %s", "Foo"),
	 *
	 *		)
	 */
	function where_and($conditions=array())
	{
		return $this->object->_where($conditions, 'AND');
	}

	function where_or($conditions=array())
	{
		return $this->object->where($conditions, 'OR');
	}


	function where($conditions=array())
	{
		return $this->object->_where($conditions, 'AND');
	}


	/** Parses the where clauses
	 * They could look like the following:
	 *
	 * array(
	 *  "post_id = 1"
	 *  array("post_id = %d", 1),
	 * )
	 *
	 * or simply "post_id = 1"
	 * @param array|string $conditions
	 * @param string $operator
	 * @return ExtensibleObject
	 */
	function _where($conditions=array(), $operator)
	{
		$where_clauses = array();

		// If conditions is not an array, make it one
		if (!is_array($conditions)) $conditions = array($conditions);

		// Is there a single condition
		if (isset($conditions[0]) && is_string($conditions['0'])) {
			$clause = $conditions[0];
			array_shift($conditions);
			$binds = $conditions;
			$where_clauses[] = $this->object->_parse_where_clause($clause,$binds);
		}

		// Are there multiple conditions
		else {
			foreach ($conditions as $condition) {
				if (is_string($condition)) {
					$where_clauses[] = $this->object->_parse_where_clause($condition);
				}
				else {
					$clause = array_shift($condition);
					$where_clauses[] = $this->object->_parse_where_clause($clause, $condition);
				}
			}
		}

		// Add where clause to query
		if ($where_clauses) $this->object->_add_where_clause($where_clauses, $operator);

		return $this->object;
	}

	/**
	 * Parses a where clause and returns an associative array
	 * representing the query
	 *
	 * E.g. parse_where_clause("post_title = %s", "Foo Bar")
	 *
	 * @global wpdb $wpdb
	 * @param string $condition
	 * @return array
	 */
	function _parse_where_clause($condition)
	{

		$column = '';
		$operator = '';
		$value = '';

		// Substitute any placeholders
		global $wpdb;
		$binds = func_get_args();

		if (isset($binds[1])) {
			$condition = $wpdb->prepare($condition, $binds[1]);
		}

		else
			$condition = $wpdb->prepare($condition);

		// Parse the where clause
		if (preg_match("/^[^\s]+/", $condition, $match)) {
			$column = trim(array_shift($match));
			$condition = str_replace($column, '', $condition);
		}
		if (preg_match("/IN|LIKE|[=!<>]+/", $condition, $match)) {
			$operator = trim(array_shift($match));
			$condition = str_replace($operator, '', $condition);
			$operatior = strtolower($operator);
			$value = trim($condition);
		}

		// Values will automatically be quoted, so remove them
		// If the value is part of an IN clause and has multiple values,
		// we attempt to split the values apart into an array and iterate
		// over them individually
		$values = preg_split("/'\s?,\s?'/", $value);

		// If there's a single value, treat it as an array so that we
		// can still iterate
		if (!$values) $values = array($value);
		foreach ($values as $index => $value) {
			$value = preg_replace("/^(\()?'/", '', $value);
			$value = preg_replace("/'(\))?$/", '', $value);
			$values[$index] = $value;
		}
		if (count($values)>1) $value = $values;

		// Return the WP Query meta query parameters
		$retval = array(
			'column'	=> $column,
			'value'		=> $value,
			'compare'	=> $operator,
			'type'		=> 'string',
		);
		if (is_numeric($value)) $retval['type'] = 'numeric';;

		return $retval;
	}

	/**
	 * Converts a stdObject to an Entity
	 * @param stdObject $stdObject
	 * @return stdObject
	 */
	function _convert_to_entity($stdObject)
	{
		$stdObject->id_field = $key = $this->object->get_primary_key_column();
		$stdObject->$key = (int) $stdObject->$key;
		return $stdObject;
	}

	/**
	 * Converts a stdObject entity to a model
	 * @param stdObject $stdObject
	 */
	function convert_to_model($stdObject, $context=FALSE)
	{
		// Create a factory
		$factory = $this->object->_get_registry()->get_singleton_utility('I_Component_Factory');
		return $factory->create($this->object->get_model_factory_method(), $this->object, $stdObject, $context);
	}

	/**
	 * Saves an entity
	 * @param stdClass|C_DataMapper_Model $entity
	 * @return bool
	 */
	function save($entity)
	{
		$retval = FALSE;

		// Attempt to use something else, most likely an associative array
		// TODO: Support assocative arrays. The trick is to support references
		// with dynamic calls using __call() and call_user_func_array().
		if (is_array($entity)) throw new E_InvalidEntityException();

		// Save stdClass objects. Don't have the ability to provide validation
		elseif (in_array(strtolower(get_class($entity)), array('stdclass', 'stdobject'))) {
			$retval = $this->_save_entity($entity);
		}

		// Save models. First, they must be validated
		elseif (is_subclass_of($entity, 'C_DataMapper_Model') or get_class($entity) == 'C_DataMapper_Model') {
			$entity->validate();
			if ($entity->is_valid()) $retval = $this->_save_entity($entity->get_entity());
		}
		else {
			throw new E_InvalidEntityException();
		}

		return $retval;
	}
}

class C_DataMapper_Driver_Base extends C_Component
{
	var $_object_name;
	var $_model_factory_method = FALSE;

	function define()
	{
		parent::define();
		$this->add_mixin('Mixin_DataMapper_Driver_Base');
		$this->implement('I_DataMapper_Driver');
	}

	function initialize($object_name, $context=FALSE)
	{
		parent::initialize($context);
		$this->_object_name = $object_name;
	}

	/**
	 * Gets the object name
	 * @return string
	 */
	function get_object_name()
	{
		return $this->_object_name;
	}

	/**
	 * Gets the name of the table
	 * @global string $table_prefix
	 * @return string
	 */
	function get_table_name()
	{
		global $table_prefix;
		return $table_prefix.$this->_object_name;
	}

	/**
	 * Sets the name of the factory method used to create a model for this entity
	 * @param string $method_name
	 */
	function set_model_factory_method($method_name)
	{
		$this->_model_factory_method = $method_name;
	}


	/**
	 * Gets the name of the factory method used to create a model for this entity
	 */
	function get_model_factory_method()
	{
		return $this->_model_factory_method;
	}


	/**
	 * Gets the name of the primary key column
	 * @return string
	 */
	function get_primary_key_column()
	{
		return $this->_primary_key_column;
	}
}