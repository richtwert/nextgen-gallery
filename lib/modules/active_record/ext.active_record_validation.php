<?php

class Ext_Active_Record_Validation extends Ext
{
    var $_errors = array();
    var $_default_msgs = array(
       "validates_presence_of"      => "%s should be present",
       "validates_presence_with"    => "%s should be present with %s",
       "validates_uniqueness_of"    => "%s should be unique",
       "validates_confirmation_of"  => "%s should match confirmation",
       "validates_exclusion_of"     => "%s is reserved",
       "validates_format_of"        => "%s is invalid",
       "validates_inclusion_of"     => "%s is not included in the list",
       "validates_numericality_of"  => "%s is not numeric",
       "validates_greather_than"    => "%s is too small",
       "validates_less_than"        => "%s is too large",
       "validates_equals"           => "%s is invalid",
    );
    
    var $_default_patterns = array(
      'email_address'   =>  "//"  
    );
    
    /**
     * Returns TRUE if a property is empty
     * @param string $var
     * @return boolean 
     */
    function is_empty($var)
    {
       return (is_null($var) OR (is_string($var) AND strlen($var) == 0));
    }
    
    
    function get_errors()
    {
        return $this->_errors;
    }
    
    
    function is_valid()
    {
        return !$this->has_errors();
    }
    
    
    function humanize_string($str)
    {
        $retval = array();
        if (is_array($str)) {;
           foreach ($str as $s) {
               $retval[] = $this->humanize_string($s);
           }
        }
        else $retval = ucfirst(str_replace('_', ' ', $str));
        
        return $retval; 
    }
    
    
    /**
     * Validates the length of a property's value
     * @param type $property
     * @param type $length
     * @param type $msg 
     */
    function validates_length_of($property, $length, $comparison_operator='=', $msg=FALSE)
    {
        $value = $this->object->$property;
        $default_msg = $this->_get_default_error_message_for(__METHOD__);
        
        if (!$this->is_empty($value)) {
            $invalid = FALSE;
            
            switch ($comparison_operator) {
                case '=':
                case '==':
                    $invalid = strlen($value) == $comparison;
                    $default_msg = $this->_get_default_error_message_for('validates_equals');
                    break;
                case '!=':
                case '!':
                    $invalid = strlen($value) != $comparison;
                    $default_msg = $this->_get_default_error_message_for('validates_equals');
                    break;
                case '<':
                    $invalid = strlen($value) < $comparion;
                    $default_msg = $this->_get_default_error_message_for('validates_less_than');
                    break;
                case '>':
                    $invalid = strlen($value) > $comparison;
                    $default_msg = $this->_get_default_error_message_for('validates_greater_than');
                    break;
                case '<=':
                    $invalid = strlen($value) <= $comparison;
                    $default_msg = $this->_get_default_error_message_for('validates_less_than');
                    break;
                case '>=':
                    $invalid = strlen($value) >= $comparion;
                    $default_msg = $this->_get_default_error_message_for('validates_greater_than');
                    break; 
            }
        }
    }
    
    
    /**
     * Validates that a property contains a numeric value. May optionally be tested against
     * other numbers.
     * @param string $property
     * @param decimal $comparison
     * @param string $comparison_operator
     * @param type $msg 
     */
    function validates_numericality_of($property, $comparison=FALSE, $comparison_operator=FALSE, $int_only=FALSE, $msg=FALSE)
    {
        $properties = is_array($property) ? $property : array($property);
        
        foreach ($properties as $property) {
            $value = $this->object->$property;
            $default_msg = $this->_get_default_error_message_for(__METHOD__);

            if (!$this->is_empty($value)) {
                $invalid = FALSE;

                if (is_numeric($value)) {

                    if ($int_only) {
                        $invalid = is_int($value);
                    }

                    if (!$invalid) {
                        switch ($comparison_operator) {
                            case '=':
                            case '==':
                                $invalid = $value == $comparison;
                                $default_msg = $this->_get_default_error_message_for('validates_equals');
                                break;
                            case '!=':
                            case '!':
                                $invalid = $value != $comparison;
                                $default_msg = $this->_get_default_error_message_for('validates_equals');
                                break;
                            case '<':
                                $invalid = $value < $comparion;
                                $default_msg = $this->_get_default_error_message_for('validates_less_than');
                                break;
                            case '>':
                                $invalid = $value > $comparison;
                                $default_msg = $this->_get_default_error_message_for('validates_greater_than');
                                break;
                            case '<=':
                                $invalid = $value <= $comparison;
                                $default_msg = $this->_get_default_error_message_for('validates_less_than');
                                break;
                            case '>=':
                                $invalid = $value >= $comparion;
                                $default_msg = $this->_get_default_error_message_for('validates_greater_than');
                                break;
                        }
                    }
                }
                else $invalid = TRUE;

                if ($invalid) {
                    if (!$msg) {
                        $error_msg = sprintf($default_msg, $property);
                    }
                    else $error_msg = $msg;
                    
                    $this->add_error($error_msg, $property);
                }
            }
        }
    }
    
    
    /**
     * Validates that a property includes a particular value
     * @param string $property
     * @param array $values
     * @param string $msg 
     */
    function validates_inclusion_of($property, $values=array(), $msg=FALSE)
    {
        if (!is_array($values)) $values = array($values);
        
        if (!in_array($this->object->$property, $values)) {
            if (!$msg) {
                $msg = $this->_get_default_error_message_for(__METHOD__);
                $msg = sprintf($msg, $this->humanize_string($property));
            }
            $this->add_error($msg, $property);
        }
    }
    
    
    /**
     * Validates that a property's value matches a particular pattern
     * @param string|array $property
     * @param string $pattern
     * @param string $msg 
     */
    function validates_format_of($property, $pattern, $msg = FALSE)
    {
        if (!is_array($property)) $property = array($property);
        
        // A pattern could be the name of a default pattern, or a regex pattern
        $default_pattern = $this->get_default_pattern_for($pattern);
        if ($default_pattern) $default_pattern = $pattern;
        
        foreach ($property as $prop) {
            
            // We do not validate blank values - we rely on "validates_presense_of"
            // for that
            if (!$this->is_empty($this->object->$prop)) {
                
                // If it doesn't match, then it's an error
                if (!preg_match($pattern, $property)) {
                    
                    // Get default message
                    if (!$msg) {
                        $msg = $this->_get_default_error_message_for(__METHOD__);
                        $msg = sprintf($msg, $this->humanize_string($property));
                    }
                    $this->add_error($msg, $property);
                }
            }            
        }
    }
    
    
    /**
     * Ensures that a property does NOT have a particular value
     * @param string $property
     * @param array $exclusions
     * @param string $msg 
     */
    function validates_exclusion_of($property, $exclusions=array(), $msg=FALSE)
    {
        $invalid=FALSE;
        
        if (!is_array($exclusions)) $exclusions = array($exclusions);
        foreach ($exclusions as $exclusion) {
            if ($exclusion == $this->object->$property) {
                $invalid = TRUE;
                break;
            }
        }
        
        if ($invalid) {
            if (!$msg) {
                $msg = $this->_get_default_error_message_for(__METHOD__);
                $msg = sprintf($msg, $this->humanize_string($property));
            }
            $this->add_error($msg, $property);
        }
    }
    
    
    /**
     * Validates the confirmation of a property
     * @param string $property
     * @param string $confirmation
     * @param string $msg 
     */
    function validates_confirmation_of($property, $confirmation, $msg=FALSE)
    {
        if ($this->object->$property != $this->object->$confirmation) {
            if (!$msg) {
                $msg = $this->_get_default_error_message_for(__METHOD__);
                $msg = sprintf($msg, $this->humanize_string($property));
            }
            $this->add_error($msg, $property);
        }
    }
    
    
    /**
     * Validates the uniqueness of a property
     * @param string $property
     * @param array $scope
     * @param string $msg 
     */
    function validates_uniqueness_of($property, $scope=array(), $msg=FALSE)
    {
        $db     = $this->object->db;
        $value  = $this->object->property;
        $query  = "SELECT COUNT({$property}) FROM {$this->object->table_name}";
        
        if ($scope) {
            if (!is_array($scope)) $scope = array($scope);
            foreach ($scope as $col) {
                $where[] = sprintf("{$col} = %s", $this->object->$col);
            }
            $query .= 'WHERE '.implode(' AND ', $where);
        }
        
        // If a row exists with the property specified, then it's not unique
        if (((int)$db->get_var($query)) >0 ) {
            
            // Get default msg
            if (!$msg) {
                $msg = $this->_get_default_error_message_for(__METHOD__);
                $msg = sprintf($msg, $this->humanize_string($property));
            }
            
            // Add error
            $this->add_error($msg, $property);
        }
    }
    
    
    /**
     * Validates the presence of a value for a particular field
     * @param string $property
     * @param string $msg 
     */
    function validates_presence_of($properties, $with=array(), $msg=FALSE)
    {   
        $missing = array();
        
        if (!is_array($properties)) $properties = array($properties);
        
        // Iterate through each property that we're to check, and ensure
        // a value is present
        foreach ($properties as $property) {
            $invalid = TRUE;
            
            // Is a value present?
            if (!$this->is_empty($this->object->$property)) {
                $invalid = FALSE;
                
                // This property must be present with at least another property
                if ($with) {
                    if (!is_array($with)) $with = array($with);
                    foreach ($with as $other) {
                        if ($this->is_empty($this->object->$other)) {
                            $invalid = TRUE;
                            $missing[] = $other;
                        }
                    }
                }
            }
            
            // Add error
            if ($invalid) {
                if (!$msg) {
                    
                    // If missing isn't empty, it means that we're to use the
                    // "with" error message
                    if ($missing) {
                        $missing = implode(', ', $this->humanize_string($missing));
                        $msg = sprintf(
                            $this->_get_default_error_message_for('validates_presence_with'),
                            $property,
                            $missing
                        );
                    }
                    
                    // Has no 'with' arguments. Use the default error msg
                    else {
                        $msg = sprintf(
                            $this->_get_default_error_message_for(__METHOD__),
                            $property
                        );
                        
                    }
                }
                
                $this->add_error($msg, $property);                
            }
        }        
    }
    
    
    /**
     * Clears all errors for the object
     */
    function clear_errors()
    {
        $this->_errors = array();
    }
    
    
    /**
     * Returns TRUE if there are validation errors
     * @return type 
     */
    function has_errors()
    {
        if ($this->_errors) return TRUE;
        else return FALSE;
    }
    
    
    /**
     * Adds an error for a particular property of the object
     * @param string $property
     * @param string $msg 
     */
    function add_error($msg, $property='*')
    {
        if (!isset($this->_errors[$property])) {
            $this->_errors[$property] = array();
        }
        
        $this->_errors[$property][] = $msg;
    }
    
    /**
     * Returns the default error message for a particular validator.
     * A hook could override this, or this class could be subclassed
     * @param string $validator
     * @return string 
     */
    function _get_default_error_message_for($validator)
    {
        $retval = FALSE;
        
        // The $validator variable is often set to __METHOD__, and many
        // forget that __METHOD__ looks like this:
        // Ext_Active_Record_Validation::validates_presence_of
        // So, we fix that
        if (strpos($validator, '::') !== FALSE) {
            $parts = explode('::', $validator);
            $validator = $parts[1];
        }
        
        // Ensure that the validator has a default error message
        if (isset($this->_default_msgs[$validator])) {
            $retval = $this->_default_msgs[$validator];
        }
        
        return $retval;
    }
    
    
    
    /**
     * Returns the default pattern for a formatter, such as an "e-mail address".
     * @param string $formatter
     * @return string 
     */
    function get_default_pattern_for($formatter)
    {
        $retval = FALSE;
        
        if (isset($this->_default_patterns[$formatter])) {
            $retval = $this->_default_patterns[$formatter];
        }
        
        return $retval;
    }
}