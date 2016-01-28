<?php



/**
 * Skeleton subclass for performing query and update operations on the 'user' table.
 *
 * @package    propel.generator.tymio
 */
class UserQuery extends BaseUserQuery {

	/**
	 * Returns a user by their fully-qualified name (FQN).
	 *
	 * @param string $loginName Full login name (i.e. "account/user").
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return User|null
	 * @throws Exception
	 */
	public function findOneByFQN($loginName, PropelPDO $con = null) {
		if ( !preg_match('`^([^/\\\\]+)[/\\\\]([^/\\\\]+)$`', $loginName, $matches) )
			return null;

		$query = clone $this;
		return $query
			->useAccountQuery()
				->filterByIdentifier($matches[1])
			->endUse()
			->findOneByName($matches[2], $con);
	}

} // UserQuery
