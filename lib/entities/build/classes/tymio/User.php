<?php



/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 * @package    propel.generator.tymio
 */
class User extends BaseUser {
	const ERROR_NOT_UNIQUE = 9901;
	const ERROR_NO_DOMAIN = 9902;
	const ERROR_EMAIL_TAKEN = 9903;

	/*
	public function save(PropelPDO $con = null) {
		if ( !$this->getDomainId() )
			throw new Exception('User must belong to a domain', self::ERROR_NO_DOMAIN);

		$query = new UserQuery();

		if ( $this->getEmail() && $query->findOneByEmail($this->getEmail()) )
			throw new Exception('The e-mail address is already used by a different user', self::ERROR_EMAIL_TAKEN);

		$query->joinDomain()
		     ->addJoin(DomainPeer::ACCOUNT_ID, AccountPeer::ID)
		     ->add(AccountPeer::ID, $this->getAccount()->getId());

		if ( $this->getId() )
			$query->add(UserPeer::ID, $this->getId(), Criteria::NOT_EQUAL);

		if ( $query->findOneByName($this->getName()) )
			throw new Exception('Username is not unique', self::ERROR_NOT_UNIQUE);

		return parent::save($con);
	}
	*/

	public function setPassword($password) {
		return $this->setPasswordHash(UserPeer::getPasswordHash($password));
	}

	/**
	 * Locks the password to disable *local* authentication.
	 *
	 * @return self
	 * @see setPassword()
	 * @uses UserPeer::getLockedPassword()
	 */
	public function lockPassword() {
		return $this->setPasswordHash(UserPeer::getLockedPassword($this->getPasswordHash()));
	}

	/**
	 * Returns the Fully Qualified Name of the user
	 *
	 * @return string
	 */
	public function getFQN(PropelPDO $con = null) {
		$account = $this->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine company account of user #'.$this->getId().' "'.$this->getName().'".');

		return $account->getIdentifier().'/'.$this->getName();
	}

	/**
	 * Returns a subordinate user by ID.
	 *
	 * @param string|int $userId The subordinate user's ID or full login name.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @throws Exception
	 * @return User
	 */
	public function getSubordinate($userId, PropelPDO $con = null) {
		$query = new UserQuery();
		$user  = (
			is_numeric($userId)
			? $query->findOneById($userId, $con)
			: $query->findOneByFQN($userId, $con)
		);

		if ( $user === null )
			throw new Exception('Invalid user ID '.$userId.'.');

		$thisAccountId = (string)$this->getAccountId();
		if ( ($thisAccountId === '') or
		     ((string)$user->getAccountId() !== $thisAccountId) or
		     (!$this->isAdmin() and ((string)$user->getDomainId() !== (string)$this->getManagerOf())) )
			throw new Exception('The selected user #'.$userId.' (account #'.$user->getAccountId().') is not assigned to user "'.$this->getFQN($con).'" (account #'.$thisAccountId.').');

		return $user;
	}

	/**
	 * Gets another domain from the same account.
	 *
	 * @param int $domainId
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return Domain
	 * @throws Exception
	 */
	public function getOtherDomain($domainId, PropelPDO $con = null) {
		$domain = DomainQuery::create()->findOneById($domainId, $con);
		if ( $domain === null )
			throw new Exception('Domain with ID '.$domainId.' not found!');

		$account = $this->getAccount($con);
		if ( ($account === null) or
			 ($domain->getAccountId() != $account->getId()) )
			throw new Exception('The domain does not belong to our account!');

		return $domain;
	}

	/**
	 * Retrieves a single property value.
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

	/**
	 * Returns whether the user is the account administrator
	 *
	 * @return bool
	 */
	public function isAdmin() {
		return (bool)$this->getIsAdmin();
	}

} // User
