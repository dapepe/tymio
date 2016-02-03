<?php


/**
 * Base class that represents a query for the 'user' table.
 *
 *
 *
 * @method UserQuery orderById($order = Criteria::ASC) Order by the id column
 * @method UserQuery orderByAccountId($order = Criteria::ASC) Order by the account_id column
 * @method UserQuery orderByDomainId($order = Criteria::ASC) Order by the domain_id column
 * @method UserQuery orderByDeleted($order = Criteria::ASC) Order by the deleted column
 * @method UserQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method UserQuery orderByFirstname($order = Criteria::ASC) Order by the firstname column
 * @method UserQuery orderByLastname($order = Criteria::ASC) Order by the lastname column
 * @method UserQuery orderByPhone($order = Criteria::ASC) Order by the phone column
 * @method UserQuery orderByManagerOf($order = Criteria::ASC) Order by the manager_of column
 * @method UserQuery orderByIsAdmin($order = Criteria::ASC) Order by the is_admin column
 * @method UserQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method UserQuery orderByPasswordHash($order = Criteria::ASC) Order by the password_hash column
 * @method UserQuery orderByNumber($order = Criteria::ASC) Order by the number column
 *
 * @method UserQuery groupById() Group by the id column
 * @method UserQuery groupByAccountId() Group by the account_id column
 * @method UserQuery groupByDomainId() Group by the domain_id column
 * @method UserQuery groupByDeleted() Group by the deleted column
 * @method UserQuery groupByName() Group by the name column
 * @method UserQuery groupByFirstname() Group by the firstname column
 * @method UserQuery groupByLastname() Group by the lastname column
 * @method UserQuery groupByPhone() Group by the phone column
 * @method UserQuery groupByManagerOf() Group by the manager_of column
 * @method UserQuery groupByIsAdmin() Group by the is_admin column
 * @method UserQuery groupByEmail() Group by the email column
 * @method UserQuery groupByPasswordHash() Group by the password_hash column
 * @method UserQuery groupByNumber() Group by the number column
 *
 * @method UserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method UserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method UserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method UserQuery leftJoinAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the Account relation
 * @method UserQuery rightJoinAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Account relation
 * @method UserQuery innerJoinAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the Account relation
 *
 * @method UserQuery leftJoinDomain($relationAlias = null) Adds a LEFT JOIN clause to the query using the Domain relation
 * @method UserQuery rightJoinDomain($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Domain relation
 * @method UserQuery innerJoinDomain($relationAlias = null) Adds a INNER JOIN clause to the query using the Domain relation
 *
 * @method UserQuery leftJoinClockingRelatedByCreatorId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ClockingRelatedByCreatorId relation
 * @method UserQuery rightJoinClockingRelatedByCreatorId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ClockingRelatedByCreatorId relation
 * @method UserQuery innerJoinClockingRelatedByCreatorId($relationAlias = null) Adds a INNER JOIN clause to the query using the ClockingRelatedByCreatorId relation
 *
 * @method UserQuery leftJoinClockingRelatedByUserId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ClockingRelatedByUserId relation
 * @method UserQuery rightJoinClockingRelatedByUserId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ClockingRelatedByUserId relation
 * @method UserQuery innerJoinClockingRelatedByUserId($relationAlias = null) Adds a INNER JOIN clause to the query using the ClockingRelatedByUserId relation
 *
 * @method UserQuery leftJoinPropertyValue($relationAlias = null) Adds a LEFT JOIN clause to the query using the PropertyValue relation
 * @method UserQuery rightJoinPropertyValue($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PropertyValue relation
 * @method UserQuery innerJoinPropertyValue($relationAlias = null) Adds a INNER JOIN clause to the query using the PropertyValue relation
 *
 * @method UserQuery leftJoinSystemLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the SystemLog relation
 * @method UserQuery rightJoinSystemLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SystemLog relation
 * @method UserQuery innerJoinSystemLog($relationAlias = null) Adds a INNER JOIN clause to the query using the SystemLog relation
 *
 * @method UserQuery leftJoinTransactionRelatedByCreatorId($relationAlias = null) Adds a LEFT JOIN clause to the query using the TransactionRelatedByCreatorId relation
 * @method UserQuery rightJoinTransactionRelatedByCreatorId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TransactionRelatedByCreatorId relation
 * @method UserQuery innerJoinTransactionRelatedByCreatorId($relationAlias = null) Adds a INNER JOIN clause to the query using the TransactionRelatedByCreatorId relation
 *
 * @method UserQuery leftJoinTransactionRelatedByUserId($relationAlias = null) Adds a LEFT JOIN clause to the query using the TransactionRelatedByUserId relation
 * @method UserQuery rightJoinTransactionRelatedByUserId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TransactionRelatedByUserId relation
 * @method UserQuery innerJoinTransactionRelatedByUserId($relationAlias = null) Adds a INNER JOIN clause to the query using the TransactionRelatedByUserId relation
 *
 * @method User findOne(PropelPDO $con = null) Return the first User matching the query
 * @method User findOneOrCreate(PropelPDO $con = null) Return the first User matching the query, or a new User object populated from the query conditions when no match is found
 *
 * @method User findOneByAccountId(int $account_id) Return the first User filtered by the account_id column
 * @method User findOneByDomainId(int $domain_id) Return the first User filtered by the domain_id column
 * @method User findOneByDeleted(int $deleted) Return the first User filtered by the deleted column
 * @method User findOneByName(string $name) Return the first User filtered by the name column
 * @method User findOneByFirstname(string $firstname) Return the first User filtered by the firstname column
 * @method User findOneByLastname(string $lastname) Return the first User filtered by the lastname column
 * @method User findOneByPhone(string $phone) Return the first User filtered by the phone column
 * @method User findOneByManagerOf(int $manager_of) Return the first User filtered by the manager_of column
 * @method User findOneByIsAdmin(int $is_admin) Return the first User filtered by the is_admin column
 * @method User findOneByEmail(string $email) Return the first User filtered by the email column
 * @method User findOneByPasswordHash(string $password_hash) Return the first User filtered by the password_hash column
 * @method User findOneByNumber(string $number) Return the first User filtered by the number column
 *
 * @method array findById(int $id) Return User objects filtered by the id column
 * @method array findByAccountId(int $account_id) Return User objects filtered by the account_id column
 * @method array findByDomainId(int $domain_id) Return User objects filtered by the domain_id column
 * @method array findByDeleted(int $deleted) Return User objects filtered by the deleted column
 * @method array findByName(string $name) Return User objects filtered by the name column
 * @method array findByFirstname(string $firstname) Return User objects filtered by the firstname column
 * @method array findByLastname(string $lastname) Return User objects filtered by the lastname column
 * @method array findByPhone(string $phone) Return User objects filtered by the phone column
 * @method array findByManagerOf(int $manager_of) Return User objects filtered by the manager_of column
 * @method array findByIsAdmin(int $is_admin) Return User objects filtered by the is_admin column
 * @method array findByEmail(string $email) Return User objects filtered by the email column
 * @method array findByPasswordHash(string $password_hash) Return User objects filtered by the password_hash column
 * @method array findByNumber(string $number) Return User objects filtered by the number column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseUserQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseUserQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'User', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new UserQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   UserQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return UserQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof UserQuery) {
            return $criteria;
        }
        $query = new UserQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   User|User[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = UserPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 User A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneById($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 User A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `account_id`, `domain_id`, `deleted`, `name`, `firstname`, `lastname`, `phone`, `manager_of`, `is_admin`, `email`, `password_hash`, `number` FROM `user` WHERE `id` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new User();
            $obj->hydrate($row);
            UserPeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return User|User[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|User[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserPeer::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id >= 12
     * $query->filterById(array('max' => 12)); // WHERE id <= 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(UserPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(UserPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::ID, $id, $comparison);
    }

    /**
     * Filter the query on the account_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAccountId(1234); // WHERE account_id = 1234
     * $query->filterByAccountId(array(12, 34)); // WHERE account_id IN (12, 34)
     * $query->filterByAccountId(array('min' => 12)); // WHERE account_id >= 12
     * $query->filterByAccountId(array('max' => 12)); // WHERE account_id <= 12
     * </code>
     *
     * @see       filterByAccount()
     *
     * @param     mixed $accountId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByAccountId($accountId = null, $comparison = null)
    {
        if (is_array($accountId)) {
            $useMinMax = false;
            if (isset($accountId['min'])) {
                $this->addUsingAlias(UserPeer::ACCOUNT_ID, $accountId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($accountId['max'])) {
                $this->addUsingAlias(UserPeer::ACCOUNT_ID, $accountId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::ACCOUNT_ID, $accountId, $comparison);
    }

    /**
     * Filter the query on the domain_id column
     *
     * Example usage:
     * <code>
     * $query->filterByDomainId(1234); // WHERE domain_id = 1234
     * $query->filterByDomainId(array(12, 34)); // WHERE domain_id IN (12, 34)
     * $query->filterByDomainId(array('min' => 12)); // WHERE domain_id >= 12
     * $query->filterByDomainId(array('max' => 12)); // WHERE domain_id <= 12
     * </code>
     *
     * @see       filterByDomain()
     *
     * @param     mixed $domainId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByDomainId($domainId = null, $comparison = null)
    {
        if (is_array($domainId)) {
            $useMinMax = false;
            if (isset($domainId['min'])) {
                $this->addUsingAlias(UserPeer::DOMAIN_ID, $domainId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($domainId['max'])) {
                $this->addUsingAlias(UserPeer::DOMAIN_ID, $domainId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::DOMAIN_ID, $domainId, $comparison);
    }

    /**
     * Filter the query on the deleted column
     *
     * Example usage:
     * <code>
     * $query->filterByDeleted(1234); // WHERE deleted = 1234
     * $query->filterByDeleted(array(12, 34)); // WHERE deleted IN (12, 34)
     * $query->filterByDeleted(array('min' => 12)); // WHERE deleted >= 12
     * $query->filterByDeleted(array('max' => 12)); // WHERE deleted <= 12
     * </code>
     *
     * @param     mixed $deleted The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByDeleted($deleted = null, $comparison = null)
    {
        if (is_array($deleted)) {
            $useMinMax = false;
            if (isset($deleted['min'])) {
                $this->addUsingAlias(UserPeer::DELETED, $deleted['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deleted['max'])) {
                $this->addUsingAlias(UserPeer::DELETED, $deleted['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::DELETED, $deleted, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $name)) {
                $name = str_replace('*', '%', $name);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::NAME, $name, $comparison);
    }

    /**
     * Filter the query on the firstname column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstname('fooValue');   // WHERE firstname = 'fooValue'
     * $query->filterByFirstname('%fooValue%'); // WHERE firstname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $firstname The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByFirstname($firstname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstname)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $firstname)) {
                $firstname = str_replace('*', '%', $firstname);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::FIRSTNAME, $firstname, $comparison);
    }

    /**
     * Filter the query on the lastname column
     *
     * Example usage:
     * <code>
     * $query->filterByLastname('fooValue');   // WHERE lastname = 'fooValue'
     * $query->filterByLastname('%fooValue%'); // WHERE lastname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastname The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByLastname($lastname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastname)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lastname)) {
                $lastname = str_replace('*', '%', $lastname);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::LASTNAME, $lastname, $comparison);
    }

    /**
     * Filter the query on the phone column
     *
     * Example usage:
     * <code>
     * $query->filterByPhone('fooValue');   // WHERE phone = 'fooValue'
     * $query->filterByPhone('%fooValue%'); // WHERE phone LIKE '%fooValue%'
     * </code>
     *
     * @param     string $phone The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByPhone($phone = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($phone)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $phone)) {
                $phone = str_replace('*', '%', $phone);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::PHONE, $phone, $comparison);
    }

    /**
     * Filter the query on the manager_of column
     *
     * Example usage:
     * <code>
     * $query->filterByManagerOf(1234); // WHERE manager_of = 1234
     * $query->filterByManagerOf(array(12, 34)); // WHERE manager_of IN (12, 34)
     * $query->filterByManagerOf(array('min' => 12)); // WHERE manager_of >= 12
     * $query->filterByManagerOf(array('max' => 12)); // WHERE manager_of <= 12
     * </code>
     *
     * @param     mixed $managerOf The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByManagerOf($managerOf = null, $comparison = null)
    {
        if (is_array($managerOf)) {
            $useMinMax = false;
            if (isset($managerOf['min'])) {
                $this->addUsingAlias(UserPeer::MANAGER_OF, $managerOf['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($managerOf['max'])) {
                $this->addUsingAlias(UserPeer::MANAGER_OF, $managerOf['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::MANAGER_OF, $managerOf, $comparison);
    }

    /**
     * Filter the query on the is_admin column
     *
     * Example usage:
     * <code>
     * $query->filterByIsAdmin(1234); // WHERE is_admin = 1234
     * $query->filterByIsAdmin(array(12, 34)); // WHERE is_admin IN (12, 34)
     * $query->filterByIsAdmin(array('min' => 12)); // WHERE is_admin >= 12
     * $query->filterByIsAdmin(array('max' => 12)); // WHERE is_admin <= 12
     * </code>
     *
     * @param     mixed $isAdmin The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByIsAdmin($isAdmin = null, $comparison = null)
    {
        if (is_array($isAdmin)) {
            $useMinMax = false;
            if (isset($isAdmin['min'])) {
                $this->addUsingAlias(UserPeer::IS_ADMIN, $isAdmin['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($isAdmin['max'])) {
                $this->addUsingAlias(UserPeer::IS_ADMIN, $isAdmin['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserPeer::IS_ADMIN, $isAdmin, $comparison);
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%'); // WHERE email LIKE '%fooValue%'
     * </code>
     *
     * @param     string $email The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByEmail($email = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $email)) {
                $email = str_replace('*', '%', $email);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::EMAIL, $email, $comparison);
    }

    /**
     * Filter the query on the password_hash column
     *
     * Example usage:
     * <code>
     * $query->filterByPasswordHash('fooValue');   // WHERE password_hash = 'fooValue'
     * $query->filterByPasswordHash('%fooValue%'); // WHERE password_hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $passwordHash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByPasswordHash($passwordHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($passwordHash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $passwordHash)) {
                $passwordHash = str_replace('*', '%', $passwordHash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::PASSWORD_HASH, $passwordHash, $comparison);
    }

    /**
     * Filter the query on the number column
     *
     * Example usage:
     * <code>
     * $query->filterByNumber('fooValue');   // WHERE number = 'fooValue'
     * $query->filterByNumber('%fooValue%'); // WHERE number LIKE '%fooValue%'
     * </code>
     *
     * @param     string $number The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function filterByNumber($number = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($number)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $number)) {
                $number = str_replace('*', '%', $number);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserPeer::NUMBER, $number, $comparison);
    }

    /**
     * Filter the query by a related Account object
     *
     * @param   Account|PropelObjectCollection $account The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAccount($account, $comparison = null)
    {
        if ($account instanceof Account) {
            return $this
                ->addUsingAlias(UserPeer::ACCOUNT_ID, $account->getId(), $comparison);
        } elseif ($account instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserPeer::ACCOUNT_ID, $account->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByAccount() only accepts arguments of type Account or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Account relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinAccount($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Account');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Account');
        }

        return $this;
    }

    /**
     * Use the Account relation Account object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   AccountQuery A secondary query class using the current class as primary query
     */
    public function useAccountQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinAccount($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Account', 'AccountQuery');
    }

    /**
     * Filter the query by a related Domain object
     *
     * @param   Domain|PropelObjectCollection $domain The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByDomain($domain, $comparison = null)
    {
        if ($domain instanceof Domain) {
            return $this
                ->addUsingAlias(UserPeer::DOMAIN_ID, $domain->getId(), $comparison);
        } elseif ($domain instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserPeer::DOMAIN_ID, $domain->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByDomain() only accepts arguments of type Domain or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Domain relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinDomain($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Domain');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Domain');
        }

        return $this;
    }

    /**
     * Use the Domain relation Domain object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   DomainQuery A secondary query class using the current class as primary query
     */
    public function useDomainQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinDomain($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Domain', 'DomainQuery');
    }

    /**
     * Filter the query by a related Clocking object
     *
     * @param   Clocking|PropelObjectCollection $clocking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByClockingRelatedByCreatorId($clocking, $comparison = null)
    {
        if ($clocking instanceof Clocking) {
            return $this
                ->addUsingAlias(UserPeer::ID, $clocking->getCreatorId(), $comparison);
        } elseif ($clocking instanceof PropelObjectCollection) {
            return $this
                ->useClockingRelatedByCreatorIdQuery()
                ->filterByPrimaryKeys($clocking->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByClockingRelatedByCreatorId() only accepts arguments of type Clocking or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ClockingRelatedByCreatorId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinClockingRelatedByCreatorId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ClockingRelatedByCreatorId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ClockingRelatedByCreatorId');
        }

        return $this;
    }

    /**
     * Use the ClockingRelatedByCreatorId relation Clocking object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   ClockingQuery A secondary query class using the current class as primary query
     */
    public function useClockingRelatedByCreatorIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinClockingRelatedByCreatorId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ClockingRelatedByCreatorId', 'ClockingQuery');
    }

    /**
     * Filter the query by a related Clocking object
     *
     * @param   Clocking|PropelObjectCollection $clocking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByClockingRelatedByUserId($clocking, $comparison = null)
    {
        if ($clocking instanceof Clocking) {
            return $this
                ->addUsingAlias(UserPeer::ID, $clocking->getUserId(), $comparison);
        } elseif ($clocking instanceof PropelObjectCollection) {
            return $this
                ->useClockingRelatedByUserIdQuery()
                ->filterByPrimaryKeys($clocking->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByClockingRelatedByUserId() only accepts arguments of type Clocking or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ClockingRelatedByUserId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinClockingRelatedByUserId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ClockingRelatedByUserId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ClockingRelatedByUserId');
        }

        return $this;
    }

    /**
     * Use the ClockingRelatedByUserId relation Clocking object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   ClockingQuery A secondary query class using the current class as primary query
     */
    public function useClockingRelatedByUserIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinClockingRelatedByUserId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ClockingRelatedByUserId', 'ClockingQuery');
    }

    /**
     * Filter the query by a related PropertyValue object
     *
     * @param   PropertyValue|PropelObjectCollection $propertyValue  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByPropertyValue($propertyValue, $comparison = null)
    {
        if ($propertyValue instanceof PropertyValue) {
            return $this
                ->addUsingAlias(UserPeer::ID, $propertyValue->getUserId(), $comparison);
        } elseif ($propertyValue instanceof PropelObjectCollection) {
            return $this
                ->usePropertyValueQuery()
                ->filterByPrimaryKeys($propertyValue->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPropertyValue() only accepts arguments of type PropertyValue or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PropertyValue relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinPropertyValue($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PropertyValue');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'PropertyValue');
        }

        return $this;
    }

    /**
     * Use the PropertyValue relation PropertyValue object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   PropertyValueQuery A secondary query class using the current class as primary query
     */
    public function usePropertyValueQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPropertyValue($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PropertyValue', 'PropertyValueQuery');
    }

    /**
     * Filter the query by a related SystemLog object
     *
     * @param   SystemLog|PropelObjectCollection $systemLog  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterBySystemLog($systemLog, $comparison = null)
    {
        if ($systemLog instanceof SystemLog) {
            return $this
                ->addUsingAlias(UserPeer::ID, $systemLog->getUserId(), $comparison);
        } elseif ($systemLog instanceof PropelObjectCollection) {
            return $this
                ->useSystemLogQuery()
                ->filterByPrimaryKeys($systemLog->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterBySystemLog() only accepts arguments of type SystemLog or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SystemLog relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinSystemLog($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SystemLog');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'SystemLog');
        }

        return $this;
    }

    /**
     * Use the SystemLog relation SystemLog object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   SystemLogQuery A secondary query class using the current class as primary query
     */
    public function useSystemLogQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinSystemLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SystemLog', 'SystemLogQuery');
    }

    /**
     * Filter the query by a related Transaction object
     *
     * @param   Transaction|PropelObjectCollection $transaction  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTransactionRelatedByCreatorId($transaction, $comparison = null)
    {
        if ($transaction instanceof Transaction) {
            return $this
                ->addUsingAlias(UserPeer::ID, $transaction->getCreatorId(), $comparison);
        } elseif ($transaction instanceof PropelObjectCollection) {
            return $this
                ->useTransactionRelatedByCreatorIdQuery()
                ->filterByPrimaryKeys($transaction->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTransactionRelatedByCreatorId() only accepts arguments of type Transaction or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TransactionRelatedByCreatorId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinTransactionRelatedByCreatorId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TransactionRelatedByCreatorId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TransactionRelatedByCreatorId');
        }

        return $this;
    }

    /**
     * Use the TransactionRelatedByCreatorId relation Transaction object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   TransactionQuery A secondary query class using the current class as primary query
     */
    public function useTransactionRelatedByCreatorIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTransactionRelatedByCreatorId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TransactionRelatedByCreatorId', 'TransactionQuery');
    }

    /**
     * Filter the query by a related Transaction object
     *
     * @param   Transaction|PropelObjectCollection $transaction  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 UserQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTransactionRelatedByUserId($transaction, $comparison = null)
    {
        if ($transaction instanceof Transaction) {
            return $this
                ->addUsingAlias(UserPeer::ID, $transaction->getUserId(), $comparison);
        } elseif ($transaction instanceof PropelObjectCollection) {
            return $this
                ->useTransactionRelatedByUserIdQuery()
                ->filterByPrimaryKeys($transaction->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTransactionRelatedByUserId() only accepts arguments of type Transaction or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TransactionRelatedByUserId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function joinTransactionRelatedByUserId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TransactionRelatedByUserId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TransactionRelatedByUserId');
        }

        return $this;
    }

    /**
     * Use the TransactionRelatedByUserId relation Transaction object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   TransactionQuery A secondary query class using the current class as primary query
     */
    public function useTransactionRelatedByUserIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTransactionRelatedByUserId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TransactionRelatedByUserId', 'TransactionQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   User $user Object to remove from the list of results
     *
     * @return UserQuery The current query, for fluid interface
     */
    public function prune($user = null)
    {
        if ($user) {
            $this->addUsingAlias(UserPeer::ID, $user->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
