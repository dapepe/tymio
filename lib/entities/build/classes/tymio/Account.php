<?php



/**
 * Skeleton subclass for representing a row from the 'account' table.
 *
 * @package    propel.generator.tymio
 */
class Account extends BaseAccount {

	/**
	 * Retrieves a single property value
	 *
	 * @param string $name Property name
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return mixed
	 */
	public function getProperty($name, PropelPDO $con = null) {
		/* @var $property Property */
		$property = PropertyQuery::create()
			->filterByAccount($this)
			->findOneByName($name);

		if ( !$property )
			return null;

		$propertyValue = PropertyValueQuery::create()
			->filterByProperty($property)
			->filterByDomainId(null)
			->filterByUserId(null)
			->findOne($con);

		return $propertyValue ? $propertyValue->getValue() : $property->getDefaultValue();
	}

	/**
	 * Stores a property.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return self Returns $this.
	 */
	public function setProperty($name, $value, PropelPDO $con = null) {
		$property = PropertyQuery::create()
			->filterByAccount($this)
			->findOneByName($name, $con);

		if ( $property === null )
			throw new Exception('Could not find property "'.$name.'".');

		/* @var PropertyValue $propertyValue */
		$propertyValue = PropertyValueQuery::create()
			->filterByDomainId(null)
			->filterByUserId(null)
			->findOneByPropertyId($property->getId(), $con);

		if ( $propertyValue === null ) {
			$propertyValue = new PropertyValue();
			$propertyValue->setProperty($property);
		}

		$propertyValue
			->set($value)
			->save($con);

		return $this;
	}

	/**
	 * Returns a list of properties.
	 *
	 * @param array $names Optional. Array with names of properties to return.
	 *     If omitted or NULL, all domain-specific properties will be returned.
	 *     Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return array An associative array mapping property names to associative
	 *     arrays of value (key "Value") and type string (key "Type").
	 */
	public function getProperties(array $names = null, PropelPDO $con = null) {
		$query = PropertyValueQuery::create()
			->joinWith('Property')
			->add(PropertyPeer::ACCOUNT_ID, $this->getId())
			->filterByDomainId(null)
			->filterByUserId(null);

		if ( is_array($names) )
			$query->filterBy(PropertyPeer::NAME, $names, Criteria::IN);

		$result = array();

		foreach ($query->find($con) as $propertyValue) { /* @var PropertyValue $propertyValue */
			$property = $propertyValue->getProperty($con);
			$result[$property->getName()] = array(
				'Value' => $propertyValue->get(),
				'Type'  => $property->getType(),
			);
		}

		return $result;
	}

	/**
	 * Sets multiple properties from an array.
	 *
	 * @param array $values Associative array mapping property names to values.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return self Returns $this.
	 */
	public function setProperties(array $values, PropelPDO $con = null) {
		if ( $con === null )
			$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			foreach ($values as $name => $value)
				$this->setProperty($name, $value, $con);
		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $this;
	}

} // Account
