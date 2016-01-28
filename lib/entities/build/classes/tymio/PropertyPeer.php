<?php



/**
 * Skeleton subclass for performing query and update operations on the 'property' table.
 *
 * @package    propel.generator.tymio
 */
class PropertyPeer extends BasePropertyPeer {

	/**
	 * Initializes a property value
	 *
	 * @param mixed $value The "raw" input value
	 * @param string $type The value type (int, float, array, bool)
	 * @param mixed $defaultValue The default value
	 * @return mixed The initialized value
	 */
	static public function initValue($value, $type = 'string', $defaultValue = '') {
		switch ($type) {
    		case 'int':
      			return ( is_numeric($value) ? (int)$value : (int)$defaultValue );
		    case 'float':
      			return ( is_numeric($value) ? (float)$value : (float)$defaultValue );
		    case 'array':
      			return ( is_array($value) ? $value : array() );
		    case 'bool':
		    	return (bool)$value;
  		}
  		return ( $value === '' ? (string)$defaultValue : $value );
	}

	/**
	 * Retrieves a single property value for the specified object.
	 *
	 * @param BaseObject $object The object to retrieve the property for. Must
	 *     be one of the following: {@link User}, {@link Domain}, {@link Account}.
	 * @param string $name Property name
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return mixed
	 * @see get()
	 */
	static public function getProperty(BaseObject $object, $name, PropelPDO $con = null) {
		/* @var $property Property */
		$property = PropertyQuery::create()->findOneByName($name, $con);

		if ( !$property )
			return null;

		$query = new PropertyValueQuery();
		$query->filterByProperty($property);

		$propertyValue = $query->{'findOneBy'.get_class($object).'Id'}($object->getId(), $con);

		return $propertyValue ? $propertyValue->getValue() : $property->getDefaultValue();
	}

	/**
	 * Stores a property.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return BaseObject Returns {@link $object}.
	 */
	static public function setProperty(BaseObject $object, $name, $value, PropelPDO $con = null) {
		$property = PropertyQuery::create()
			->filterByAccount($object->getAccount($con))
			->findOneByName($name, $con);

		if ( $property === null )
			throw new Exception('Could not find property "'.$name.'".');

		$class = get_class($object);

		/* @var PropertyValue $propertyValue */
		$propertyValue = PropertyValueQuery::create()
			->{'filterBy'.$class}($object)
			->findOneByPropertyId($property->getId(), $con);

		if ( $propertyValue === null ) {
			$propertyValue = new PropertyValue();
			$propertyValue
				->setProperty($property)
				->{'set'.$class}($object);
		}

		$propertyValue
			->set($value)
			->save($con);

		return $object;
	}

	/**
	 * Returns a list of properties with values.
	 *
	 * @param array $names Optional. Array with names of properties to return.
	 *     If omitted or NULL, all domain-specific properties will be returned.
	 *     Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return array An associative array mapping property names to associative
	 *     arrays of value (key "Value") and type string (key "Type").
	 */
	static public function getProperties(BaseObject $object, array $names = null, PropelPDO $con = null) {
		$query = PropertyValueQuery::create()
			->joinWith('Property');

		$class = get_class($object);
		switch ( $class ) {
			case 'Account':
				$query->add(PropertyPeer::ACCOUNT_ID, $object->getId());
				break;

			case 'Domain':
			case 'User':
				$query->{'filterBy'.$class}($object);
				break;

			default:
				throw new Exception('Object has unsupported class "'.$class.'".');
		}

		if ( is_array($names) )
			$query->add(PropertyPeer::NAME, $names, Criteria::IN);

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
	 * @return BaseObject Returns {@link $object}.
	 */
	static public function setProperties(BaseObject $object, array $values, PropelPDO $con = null) {
		if ( $con === null )
			$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			foreach ($values as $name => $value)
				$object->setProperty($name, $value, $con);
		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $object;
	}

	static private function createPropertyValueQuery($accountId, $domainId, $userId) {
		$query = PropertyValueQuery::create()
			->joinProperty();

		if ( $accountId !== null )
			$query->add(PropertyPeer::ACCOUNT_ID, $accountId);
		if ( $domainId !== null )
			$query->add(PropertyValuePeer::DOMAIN_ID, $domainId);
		if ( $userId !== null )
			$query->add(PropertyValuePeer::USER_ID, $userId);

		return $query;
	}

	static public function getDefaults(Account $account, PropelPDO $con = null) {
		$items = PropertyQuery::create()
			->findByAccountId($account->getId(), $con);

		$result = array();

		foreach ($items as $property)
			$result[$property->getName()] = PropertyPeer::initValue(json_decode($property->getDefaultValue(), true), $property->getType());

		return $result;
	}

	/**
	 * Returns an associative array of defined properties.
	 *
	 * If a property has no value specified, its default value is returned.
	 *
	 * @param Account $account
	 * @param Domain $domain Optional. Default is NULL.
	 * @param User $user Optional. Default is NULL.
	 * @param array $filter Optional. An array with names of properties to
	 *     retrieve values for. If NULL, all values will be returned.
	 *     Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return array An associative array mapping property names to values as
	 *     obtained by {@link PropertyValue::get()}.
	 */
	static public function getAll(Account $account, Domain $domain = null, User $user = null, array $filter = null, PropelPDO $con = null) {
		$accountId = $account->getId();
		$domainId  = ( $domain === null ? null : $domain->getId() );
		$userId    = ( $user === null ? null : $user->getId() );

		$query     = self::createPropertyValueQuery($accountId, $domainId, $userId);
		if ( $filter !== null )
			$query->add(PropertyPeer::NAME, $filter, Criteria::IN);

		$values    = $query->find($con);

		$values->populateRelation('Property', null, $con);

		$accountValues = self::getDefaults($account, $con);
		$domainValues  = array();
		$userValues    = array();

		foreach ($values as $value) {
			$property = $value->getProperty($con);
			$name     = $property->getName();

			if ( $value->getUserId() === $userId )
				$userValues[$name] = $value->get($con);
			elseif ( ($value->getDomainId() === $domainId) and ($domainId !== null) )
				$domainValues[$name] = $value->get($con);
			elseif ( ($property->getAccountId() === $accountId) and ($accountId !== null) )
				$accountValues[$name] = $value->get($con);
		}

		$result = $userValues + $domainValues + $accountValues;
		ksort($result);

		return $result;
	}

	/**
	 * Retrieves the most-specific property value for the specified user, domain or account.
	 *
	 * If a user does not have a specific value set, the user's domain is
	 * searched for the value, and if this does not yield anything, the account
	 * will be scanned for the default value.
	 *
	 * @param string $name The property name.
	 * @param Account $account The account defining the properties.
	 * @param Domain $domain Optional. Default is NULL.
	 * @param User $user Optional. Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return mixed The property value, or NULL if no property is found.
	 * @see getProperty()
	 */
	static public function get($name, Account $account, Domain $domain = null, User $user = null, PropelPDO $con = null) {
		$property  = PropertyQuery::create()
			->filterByAccount($account)
			->findOneByName($name, $con);

		if ( $property === null )
			return null;

		$accountId = $account->getId();
		$domainId  = ( $domain === null ? null : $domain->getId() );
		$userId    = ( $user === null ? null : $user->getId() );

		$values = self::createPropertyValueQuery($accountId, null, null)
			->findByPropertyId($property->getId(), $con);

		$result = PropertyPeer::initValue(json_decode($property->getDefaultValue(), true), $property->getType());

		foreach ($values as $value) {
			$valueUserId = $value->getUserId();

			if ( ($valueUserId !== null) and ($valueUserId === $userId) )
				return $value->get($con);
			elseif ( $valueUserId !== null )
				continue;
			elseif ( ($value->getDomainId() === $domainId) and ($domainId !== null) )
				$result = $value->get($con);
		}

		return $result;
	}

} // PropertyPeer
