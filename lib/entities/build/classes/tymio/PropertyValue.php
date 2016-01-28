<?php



/**
 * Skeleton subclass for representing a row from the 'property_value' table.
 *
 * @package    propel.generator.tymio
 */
class PropertyValue extends BasePropertyValue {

	/**
	 * Get the [value] column value.
	 *
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return     string
	 */
	public function get(PropelPDO $con = null) {
		$property = $this->getProperty($con);
		if ( $property === null )
			throw new Exception('Could not get property definition for property value #'.$this->id.'.');

		$value = json_decode($this->value, true);
		if ( $value === null )
			throw new Exception('Undefined or invalid property value #'.$this->id.'.');

		return PropertyPeer::initValue(
			$value,
			$property->getType(),
			$property->getDefaultValue()
		);
	}

	/**
	 * Set the value of [value] column.
	 *
	 * @param      string $v new value
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return     PropertyValue The current object (for fluent API support)
	 */
	public function set($v, PropelPDO $con = null) {
		$property = $this->getProperty($con);
		if ( $property === null )
			throw new Exception('Could not get property definition for property value #'.$this->id.'.');

		$v = PropertyPeer::initValue($v, $property->getType());
		return $this->setValue(json_encode($v));
	}

} // PropertyValue
