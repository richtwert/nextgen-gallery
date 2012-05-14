<?php

class C_Photocrati_Serialization
{
	/*
	 * Serializes data to JSON
	 * @param mixed $value
	 * @return mixed
	 */
	static function serialize($value)
	{
		//Using json_encode here because PHP's serialize is not Unicode safe
		return json_encode($value);
	}

	/**
	 * Deserialized data from JSON or PHP serialize()
	 * @param string $value
	 * @return mixed
	 */
	static function unserialize($value)
	{
		$retval = stripcslashes($value);

		if (strlen($value) > 1)
		{
			//Using json_decode here because PHP's unserialize is not Unicode safe
			$retval = json_decode($retval, TRUE);

			// JSON Decoding failed. Perhaps it's PHP serialized data?
			if ($retval == NULL) {
				$er = error_reporting(0);
				$retval = unserialize($value);
				error_reporting($er);
			}
		}

		return $retval;
	}
}