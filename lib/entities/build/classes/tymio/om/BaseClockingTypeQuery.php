<?php


/**
 * Base class that represents a query for the 'clocking_type' table.
 *
 *
 *
 * @method ClockingTypeQuery orderById($order = Criteria::ASC) Order by the id column
 * @method ClockingTypeQuery orderByAccountId($order = Criteria::ASC) Order by the account_id column
 * @method ClockingTypeQuery orderByIdentifier($order = Criteria::ASC) Order by the identifier column
 * @method ClockingTypeQuery orderByLabel($order = Criteria::ASC) Order by the label column
 * @method ClockingTypeQuery orderByWholeDay($order = Criteria::ASC) Order by the whole_day column
 * @method ClockingTypeQuery orderByFutureGraceTime($order = Criteria::ASC) Order by the future_grace_time column
 * @method ClockingTypeQuery orderByPastGraceTime($order = Criteria::ASC) Order by the past_grace_time column
 * @method ClockingTypeQuery orderByApprovalRequired($order = Criteria::ASC) Order by the approval_required column
 *
 * @method ClockingTypeQuery groupById() Group by the id column
 * @method ClockingTypeQuery groupByAccountId() Group by the account_id column
 * @method ClockingTypeQuery groupByIdentifier() Group by the identifier column
 * @method ClockingTypeQuery groupByLabel() Group by the label column
 * @method ClockingTypeQuery groupByWholeDay() Group by the whole_day column
 * @method ClockingTypeQuery groupByFutureGraceTime() Group by the future_grace_time column
 * @method ClockingTypeQuery groupByPastGraceTime() Group by the past_grace_time column
 * @method ClockingTypeQuery groupByApprovalRequired() Group by the approval_required column
 *
 * @method ClockingTypeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method ClockingTypeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method ClockingTypeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method ClockingTypeQuery leftJoinAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the Account relation
 * @method ClockingTypeQuery rightJoinAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Account relation
 * @method ClockingTypeQuery innerJoinAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the Account relation
 *
 * @method ClockingTypeQuery leftJoinClocking($relationAlias = null) Adds a LEFT JOIN clause to the query using the Clocking relation
 * @method ClockingTypeQuery rightJoinClocking($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Clocking relation
 * @method ClockingTypeQuery innerJoinClocking($relationAlias = null) Adds a INNER JOIN clause to the query using the Clocking relation
 *
 * @method ClockingType findOne(PropelPDO $con = null) Return the first ClockingType matching the query
 * @method ClockingType findOneOrCreate(PropelPDO $con = null) Return the first ClockingType matching the query, or a new ClockingType object populated from the query conditions when no match is found
 *
 * @method ClockingType findOneById(int $id) Return the first ClockingType filtered by the id column
 * @method ClockingType findOneByAccountId(int $account_id) Return the first ClockingType filtered by the account_id column
 * @method ClockingType findOneByIdentifier(string $identifier) Return the first ClockingType filtered by the identifier column
 * @method ClockingType findOneByLabel(string $label) Return the first ClockingType filtered by the label column
 * @method ClockingType findOneByWholeDay(boolean $whole_day) Return the first ClockingType filtered by the whole_day column
 * @method ClockingType findOneByFutureGraceTime(string $future_grace_time) Return the first ClockingType filtered by the future_grace_time column
 * @method ClockingType findOneByPastGraceTime(string $past_grace_time) Return the first ClockingType filtered by the past_grace_time column
 * @method ClockingType findOneByApprovalRequired(boolean $approval_required) Return the first ClockingType filtered by the approval_required column
 *
 * @method array findById(int $id) Return ClockingType objects filtered by the id column
 * @method array findByAccountId(int $account_id) Return ClockingType objects filtered by the account_id column
 * @method array findByIdentifier(string $identifier) Return ClockingType objects filtered by the identifier column
 * @method array findByLabel(string $label) Return ClockingType objects filtered by the label column
 * @method array findByWholeDay(boolean $whole_day) Return ClockingType objects filtered by the whole_day column
 * @method array findByFutureGraceTime(string $future_grace_time) Return ClockingType objects filtered by the future_grace_time column
 * @method array findByPastGraceTime(string $past_grace_time) Return ClockingType objects filtered by the past_grace_time column
 * @method array findByApprovalRequired(boolean $approval_required) Return ClockingType objects filtered by the approval_required column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseClockingTypeQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseClockingTypeQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'ClockingType', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ClockingTypeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   ClockingTypeQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return ClockingTypeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof ClockingTypeQuery) {
            return $criteria;
        }
        $query = new ClockingTypeQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array $key Primary key to use for the query
                         A Primary key composition: [$id, $account_id]
     * @param     PropelPDO $con an optional connection object
     *
     * @return   ClockingType|ClockingType[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ClockingTypePeer::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1]))))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(ClockingTypePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 ClockingType A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `account_id`, `identifier`, `label`, `whole_day`, `future_grace_time`, `past_grace_time`, `approval_required` FROM `clocking_type` WHERE `id` = :p0 AND `account_id` = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new ClockingType();
            $obj->hydrate($row);
            ClockingTypePeer::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1])));
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
     * @return ClockingType|ClockingType[]|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|ClockingType[]|mixed the list of results, formatted by the current formatter
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
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(ClockingTypePeer::ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(ClockingTypePeer::ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(ClockingTypePeer::ACCOUNT_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
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
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ClockingTypePeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ClockingTypePeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::ID, $id, $comparison);
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
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByAccountId($accountId = null, $comparison = null)
    {
        if (is_array($accountId)) {
            $useMinMax = false;
            if (isset($accountId['min'])) {
                $this->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $accountId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($accountId['max'])) {
                $this->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $accountId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $accountId, $comparison);
    }

    /**
     * Filter the query on the identifier column
     *
     * Example usage:
     * <code>
     * $query->filterByIdentifier('fooValue');   // WHERE identifier = 'fooValue'
     * $query->filterByIdentifier('%fooValue%'); // WHERE identifier LIKE '%fooValue%'
     * </code>
     *
     * @param     string $identifier The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByIdentifier($identifier = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($identifier)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $identifier)) {
                $identifier = str_replace('*', '%', $identifier);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::IDENTIFIER, $identifier, $comparison);
    }

    /**
     * Filter the query on the label column
     *
     * Example usage:
     * <code>
     * $query->filterByLabel('fooValue');   // WHERE label = 'fooValue'
     * $query->filterByLabel('%fooValue%'); // WHERE label LIKE '%fooValue%'
     * </code>
     *
     * @param     string $label The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByLabel($label = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($label)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $label)) {
                $label = str_replace('*', '%', $label);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::LABEL, $label, $comparison);
    }

    /**
     * Filter the query on the whole_day column
     *
     * Example usage:
     * <code>
     * $query->filterByWholeDay(true); // WHERE whole_day = true
     * $query->filterByWholeDay('yes'); // WHERE whole_day = true
     * </code>
     *
     * @param     boolean|string $wholeDay The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByWholeDay($wholeDay = null, $comparison = null)
    {
        if (is_string($wholeDay)) {
            $wholeDay = in_array(strtolower($wholeDay), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ClockingTypePeer::WHOLE_DAY, $wholeDay, $comparison);
    }

    /**
     * Filter the query on the future_grace_time column
     *
     * Example usage:
     * <code>
     * $query->filterByFutureGraceTime(1234); // WHERE future_grace_time = 1234
     * $query->filterByFutureGraceTime(array(12, 34)); // WHERE future_grace_time IN (12, 34)
     * $query->filterByFutureGraceTime(array('min' => 12)); // WHERE future_grace_time >= 12
     * $query->filterByFutureGraceTime(array('max' => 12)); // WHERE future_grace_time <= 12
     * </code>
     *
     * @param     mixed $futureGraceTime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByFutureGraceTime($futureGraceTime = null, $comparison = null)
    {
        if (is_array($futureGraceTime)) {
            $useMinMax = false;
            if (isset($futureGraceTime['min'])) {
                $this->addUsingAlias(ClockingTypePeer::FUTURE_GRACE_TIME, $futureGraceTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($futureGraceTime['max'])) {
                $this->addUsingAlias(ClockingTypePeer::FUTURE_GRACE_TIME, $futureGraceTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::FUTURE_GRACE_TIME, $futureGraceTime, $comparison);
    }

    /**
     * Filter the query on the past_grace_time column
     *
     * Example usage:
     * <code>
     * $query->filterByPastGraceTime(1234); // WHERE past_grace_time = 1234
     * $query->filterByPastGraceTime(array(12, 34)); // WHERE past_grace_time IN (12, 34)
     * $query->filterByPastGraceTime(array('min' => 12)); // WHERE past_grace_time >= 12
     * $query->filterByPastGraceTime(array('max' => 12)); // WHERE past_grace_time <= 12
     * </code>
     *
     * @param     mixed $pastGraceTime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByPastGraceTime($pastGraceTime = null, $comparison = null)
    {
        if (is_array($pastGraceTime)) {
            $useMinMax = false;
            if (isset($pastGraceTime['min'])) {
                $this->addUsingAlias(ClockingTypePeer::PAST_GRACE_TIME, $pastGraceTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($pastGraceTime['max'])) {
                $this->addUsingAlias(ClockingTypePeer::PAST_GRACE_TIME, $pastGraceTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingTypePeer::PAST_GRACE_TIME, $pastGraceTime, $comparison);
    }

    /**
     * Filter the query on the approval_required column
     *
     * Example usage:
     * <code>
     * $query->filterByApprovalRequired(true); // WHERE approval_required = true
     * $query->filterByApprovalRequired('yes'); // WHERE approval_required = true
     * </code>
     *
     * @param     boolean|string $approvalRequired The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function filterByApprovalRequired($approvalRequired = null, $comparison = null)
    {
        if (is_string($approvalRequired)) {
            $approvalRequired = in_array(strtolower($approvalRequired), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ClockingTypePeer::APPROVAL_REQUIRED, $approvalRequired, $comparison);
    }

    /**
     * Filter the query by a related Account object
     *
     * @param   Account|PropelObjectCollection $account The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingTypeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAccount($account, $comparison = null)
    {
        if ($account instanceof Account) {
            return $this
                ->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $account->getId(), $comparison);
        } elseif ($account instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ClockingTypePeer::ACCOUNT_ID, $account->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return ClockingTypeQuery The current query, for fluid interface
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
     * Filter the query by a related Clocking object
     *
     * @param   Clocking|PropelObjectCollection $clocking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingTypeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByClocking($clocking, $comparison = null)
    {
        if ($clocking instanceof Clocking) {
            return $this
                ->addUsingAlias(ClockingTypePeer::ID, $clocking->getTypeId(), $comparison);
        } elseif ($clocking instanceof PropelObjectCollection) {
            return $this
                ->useClockingQuery()
                ->filterByPrimaryKeys($clocking->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByClocking() only accepts arguments of type Clocking or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Clocking relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function joinClocking($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Clocking');

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
            $this->addJoinObject($join, 'Clocking');
        }

        return $this;
    }

    /**
     * Use the Clocking relation Clocking object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   ClockingQuery A secondary query class using the current class as primary query
     */
    public function useClockingQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinClocking($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Clocking', 'ClockingQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ClockingType $clockingType Object to remove from the list of results
     *
     * @return ClockingTypeQuery The current query, for fluid interface
     */
    public function prune($clockingType = null)
    {
        if ($clockingType) {
            $this->addCond('pruneCond0', $this->getAliasedColName(ClockingTypePeer::ID), $clockingType->getId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(ClockingTypePeer::ACCOUNT_ID), $clockingType->getAccountId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

}
