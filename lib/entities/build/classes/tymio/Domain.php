<?php



/**
 * Skeleton subclass for representing a row from the 'domain' table.
 *
 * @package    propel.generator.tymio
 */
class Domain extends BaseDomain {
	const ERROR_NOT_UNIQUE = 9901;

	public function save(PropelPDO $con = null) {
		$query = DomainQuery::create()
		    ->filterByName($this->getName());

		if ( $this->getId() )
			$query->add(DomainPeer::ID, $this->getId(), Criteria::NOT_EQUAL);

		if ( !$this->getAccountId() )
			throw new Exception('A Domain must belong to an Account.');

		if ( $query->findOneByAccountId($this->getAccountId()) )
			throw new Exception('A Domain must have a unique name.', self::ERROR_NOT_UNIQUE);

		return parent::save($con);
	}

	/**
	 * Retrieves a single property value
	 *
	 * @param string $name Property name
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return mixed
	 */
	public function getProperty($name, PropelPDO $con = null) {
		return PropertyPeer::getProperty($this, $name, $con);
	}

	/**
	 * Stores a property.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return self
	 */
	public function setProperty($name, $value, PropelPDO $con = null) {
		return PropertyPeer::setProperty($this, $name, $value, $con);
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
		return PropertyPeer::getProperties($this, $names, $con);
	}

	/**
	 * Sets multiple properties from an array.
	 *
	 * @param array $values Associative array mapping property names to values.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return self
	 */
	public function setProperties(array $values, PropelPDO $con = null) {
		return PropertyPeer::setProperties($this, $values, $con);
	}

} // Domain
