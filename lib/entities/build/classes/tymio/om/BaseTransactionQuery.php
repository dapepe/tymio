<?php


/**
 * Base class that represents a query for the 'transaction' table.
 *
 *
 *
 * @method TransactionQuery orderById($order = Criteria::ASC) Order by the id column
 * @method TransactionQuery orderByCreatorId($order = Criteria::ASC) Order by the creator_id column
 * @method TransactionQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method TransactionQuery orderByDeleted($order = Criteria::ASC) Order by the deleted column
 * @method TransactionQuery orderByStart($order = Criteria::ASC) Order by the start column
 * @method TransactionQuery orderByEnd($order = Criteria::ASC) Order by the end column
 * @method TransactionQuery orderByCreationdate($order = Criteria::ASC) Order by the creationdate column
 * @method TransactionQuery orderByComment($order = Criteria::ASC) Order by the comment column
 *
 * @method TransactionQuery groupById() Group by the id column
 * @method TransactionQuery groupByCreatorId() Group by the creator_id column
 * @method TransactionQuery groupByUserId() Group by the user_id column
 * @method TransactionQuery groupByDeleted() Group by the deleted column
 * @method TransactionQuery groupByStart() Group by the start column
 * @method TransactionQuery groupByEnd() Group by the end column
 * @method TransactionQuery groupByCreationdate() Group by the creationdate column
 * @method TransactionQuery groupByComment() Group by the comment column
 *
 * @method TransactionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method TransactionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method TransactionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method TransactionQuery leftJoinUserRelatedByCreatorId($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRelatedByCreatorId relation
 * @method TransactionQuery rightJoinUserRelatedByCreatorId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRelatedByCreatorId relation
 * @method TransactionQuery innerJoinUserRelatedByCreatorId($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRelatedByCreatorId relation
 *
 * @method TransactionQuery leftJoinUserRelatedByUserId($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRelatedByUserId relation
 * @method TransactionQuery rightJoinUserRelatedByUserId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRelatedByUserId relation
 * @method TransactionQuery innerJoinUserRelatedByUserId($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRelatedByUserId relation
 *
 * @method TransactionQuery leftJoinBooking($relationAlias = null) Adds a LEFT JOIN clause to the query using the Booking relation
 * @method TransactionQuery rightJoinBooking($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Booking relation
 * @method TransactionQuery innerJoinBooking($relationAlias = null) Adds a INNER JOIN clause to the query using the Booking relation
 *
 * @method TransactionQuery leftJoinTransactionClocking($relationAlias = null) Adds a LEFT JOIN clause to the query using the TransactionClocking relation
 * @method TransactionQuery rightJoinTransactionClocking($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TransactionClocking relation
 * @method TransactionQuery innerJoinTransactionClocking($relationAlias = null) Adds a INNER JOIN clause to the query using the TransactionClocking relation
 *
 * @method Transaction findOne(PropelPDO $con = null) Return the first Transaction matching the query
 * @method Transaction findOneOrCreate(PropelPDO $con = null) Return the first Transaction matching the query, or a new Transaction object populated from the query conditions when no match is found
 *
 * @method Transaction findOneByCreatorId(int $creator_id) Return the first Transaction filtered by the creator_id column
 * @method Transaction findOneByUserId(int $user_id) Return the first Transaction filtered by the user_id column
 * @method Transaction findOneByDeleted(int $deleted) Return the first Transaction filtered by the deleted column
 * @method Transaction findOneByStart(string $start) Return the first Transaction filtered by the start column
 * @method Transaction findOneByEnd(string $end) Return the first Transaction filtered by the end column
 * @method Transaction findOneByCreationdate(int $creationdate) Return the first Transaction filtered by the creationdate column
 * @method Transaction findOneByComment(string $comment) Return the first Transaction filtered by the comment column
 *
 * @method array findById(int $id) Return Transaction objects filtered by the id column
 * @method array findByCreatorId(int $creator_id) Return Transaction objects filtered by the creator_id column
 * @method array findByUserId(int $user_id) Return Transaction objects filtered by the user_id column
 * @method array findByDeleted(int $deleted) Return Transaction objects filtered by the deleted column
 * @method array findByStart(string $start) Return Transaction objects filtered by the start column
 * @method array findByEnd(string $end) Return Transaction objects filtered by the end column
 * @method array findByCreationdate(int $creationdate) Return Transaction objects filtered by the creationdate column
 * @method array findByComment(string $comment) Return Transaction objects filtered by the comment column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseTransactionQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseTransactionQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'Transaction', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new TransactionQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   TransactionQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return TransactionQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof TransactionQuery) {
            return $criteria;
        }
        $query = new TransactionQuery();
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
     * @return   Transaction|Transaction[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = TransactionPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 Transaction A model object, or null if the key is not found
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
     * @return                 Transaction A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `creator_id`, `user_id`, `deleted`, `start`, `end`, `creationdate`, `comment` FROM `transaction` WHERE `id` = :p0';
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
            $obj = new Transaction();
            $obj->hydrate($row);
            TransactionPeer::addInstanceToPool($obj, (string) $key);
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
     * @return Transaction|Transaction[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|Transaction[]|mixed the list of results, formatted by the current formatter
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
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(TransactionPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(TransactionPeer::ID, $keys, Criteria::IN);
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
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(TransactionPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(TransactionPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::ID, $id, $comparison);
    }

    /**
     * Filter the query on the creator_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatorId(1234); // WHERE creator_id = 1234
     * $query->filterByCreatorId(array(12, 34)); // WHERE creator_id IN (12, 34)
     * $query->filterByCreatorId(array('min' => 12)); // WHERE creator_id >= 12
     * $query->filterByCreatorId(array('max' => 12)); // WHERE creator_id <= 12
     * </code>
     *
     * @see       filterByUserRelatedByCreatorId()
     *
     * @param     mixed $creatorId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByCreatorId($creatorId = null, $comparison = null)
    {
        if (is_array($creatorId)) {
            $useMinMax = false;
            if (isset($creatorId['min'])) {
                $this->addUsingAlias(TransactionPeer::CREATOR_ID, $creatorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($creatorId['max'])) {
                $this->addUsingAlias(TransactionPeer::CREATOR_ID, $creatorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::CREATOR_ID, $creatorId, $comparison);
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id >= 12
     * $query->filterByUserId(array('max' => 12)); // WHERE user_id <= 12
     * </code>
     *
     * @see       filterByUserRelatedByUserId()
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(TransactionPeer::USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(TransactionPeer::USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::USER_ID, $userId, $comparison);
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
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByDeleted($deleted = null, $comparison = null)
    {
        if (is_array($deleted)) {
            $useMinMax = false;
            if (isset($deleted['min'])) {
                $this->addUsingAlias(TransactionPeer::DELETED, $deleted['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deleted['max'])) {
                $this->addUsingAlias(TransactionPeer::DELETED, $deleted['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::DELETED, $deleted, $comparison);
    }

    /**
     * Filter the query on the start column
     *
     * Example usage:
     * <code>
     * $query->filterByStart('2011-03-14'); // WHERE start = '2011-03-14'
     * $query->filterByStart('now'); // WHERE start = '2011-03-14'
     * $query->filterByStart(array('max' => 'yesterday')); // WHERE start > '2011-03-13'
     * </code>
     *
     * @param     mixed $start The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByStart($start = null, $comparison = null)
    {
        if (is_array($start)) {
            $useMinMax = false;
            if (isset($start['min'])) {
                $this->addUsingAlias(TransactionPeer::START, $start['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($start['max'])) {
                $this->addUsingAlias(TransactionPeer::START, $start['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::START, $start, $comparison);
    }

    /**
     * Filter the query on the end column
     *
     * Example usage:
     * <code>
     * $query->filterByEnd('2011-03-14'); // WHERE end = '2011-03-14'
     * $query->filterByEnd('now'); // WHERE end = '2011-03-14'
     * $query->filterByEnd(array('max' => 'yesterday')); // WHERE end > '2011-03-13'
     * </code>
     *
     * @param     mixed $end The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByEnd($end = null, $comparison = null)
    {
        if (is_array($end)) {
            $useMinMax = false;
            if (isset($end['min'])) {
                $this->addUsingAlias(TransactionPeer::END, $end['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($end['max'])) {
                $this->addUsingAlias(TransactionPeer::END, $end['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::END, $end, $comparison);
    }

    /**
     * Filter the query on the creationdate column
     *
     * Example usage:
     * <code>
     * $query->filterByCreationdate(1234); // WHERE creationdate = 1234
     * $query->filterByCreationdate(array(12, 34)); // WHERE creationdate IN (12, 34)
     * $query->filterByCreationdate(array('min' => 12)); // WHERE creationdate >= 12
     * $query->filterByCreationdate(array('max' => 12)); // WHERE creationdate <= 12
     * </code>
     *
     * @param     mixed $creationdate The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByCreationdate($creationdate = null, $comparison = null)
    {
        if (is_array($creationdate)) {
            $useMinMax = false;
            if (isset($creationdate['min'])) {
                $this->addUsingAlias(TransactionPeer::CREATIONDATE, $creationdate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($creationdate['max'])) {
                $this->addUsingAlias(TransactionPeer::CREATIONDATE, $creationdate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionPeer::CREATIONDATE, $creationdate, $comparison);
    }

    /**
     * Filter the query on the comment column
     *
     * Example usage:
     * <code>
     * $query->filterByComment('fooValue');   // WHERE comment = 'fooValue'
     * $query->filterByComment('%fooValue%'); // WHERE comment LIKE '%fooValue%'
     * </code>
     *
     * @param     string $comment The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function filterByComment($comment = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($comment)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $comment)) {
                $comment = str_replace('*', '%', $comment);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(TransactionPeer::COMMENT, $comment, $comparison);
    }

    /**
     * Filter the query by a related User object
     *
     * @param   User|PropelObjectCollection $user The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByUserRelatedByCreatorId($user, $comparison = null)
    {
        if ($user instanceof User) {
            return $this
                ->addUsingAlias(TransactionPeer::CREATOR_ID, $user->getId(), $comparison);
        } elseif ($user instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TransactionPeer::CREATOR_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByUserRelatedByCreatorId() only accepts arguments of type User or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserRelatedByCreatorId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function joinUserRelatedByCreatorId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserRelatedByCreatorId');

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
            $this->addJoinObject($join, 'UserRelatedByCreatorId');
        }

        return $this;
    }

    /**
     * Use the UserRelatedByCreatorId relation User object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   UserQuery A secondary query class using the current class as primary query
     */
    public function useUserRelatedByCreatorIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinUserRelatedByCreatorId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserRelatedByCreatorId', 'UserQuery');
    }

    /**
     * Filter the query by a related User object
     *
     * @param   User|PropelObjectCollection $user The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByUserRelatedByUserId($user, $comparison = null)
    {
        if ($user instanceof User) {
            return $this
                ->addUsingAlias(TransactionPeer::USER_ID, $user->getId(), $comparison);
        } elseif ($user instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TransactionPeer::USER_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByUserRelatedByUserId() only accepts arguments of type User or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserRelatedByUserId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function joinUserRelatedByUserId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserRelatedByUserId');

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
            $this->addJoinObject($join, 'UserRelatedByUserId');
        }

        return $this;
    }

    /**
     * Use the UserRelatedByUserId relation User object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   UserQuery A secondary query class using the current class as primary query
     */
    public function useUserRelatedByUserIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserRelatedByUserId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserRelatedByUserId', 'UserQuery');
    }

    /**
     * Filter the query by a related Booking object
     *
     * @param   Booking|PropelObjectCollection $booking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBooking($booking, $comparison = null)
    {
        if ($booking instanceof Booking) {
            return $this
                ->addUsingAlias(TransactionPeer::ID, $booking->getTransactionId(), $comparison);
        } elseif ($booking instanceof PropelObjectCollection) {
            return $this
                ->useBookingQuery()
                ->filterByPrimaryKeys($booking->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBooking() only accepts arguments of type Booking or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Booking relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function joinBooking($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Booking');

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
            $this->addJoinObject($join, 'Booking');
        }

        return $this;
    }

    /**
     * Use the Booking relation Booking object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   BookingQuery A secondary query class using the current class as primary query
     */
    public function useBookingQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBooking($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Booking', 'BookingQuery');
    }

    /**
     * Filter the query by a related TransactionClocking object
     *
     * @param   TransactionClocking|PropelObjectCollection $transactionClocking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTransactionClocking($transactionClocking, $comparison = null)
    {
        if ($transactionClocking instanceof TransactionClocking) {
            return $this
                ->addUsingAlias(TransactionPeer::ID, $transactionClocking->getTransactionId(), $comparison);
        } elseif ($transactionClocking instanceof PropelObjectCollection) {
            return $this
                ->useTransactionClockingQuery()
                ->filterByPrimaryKeys($transactionClocking->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTransactionClocking() only accepts arguments of type TransactionClocking or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TransactionClocking relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function joinTransactionClocking($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TransactionClocking');

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
            $this->addJoinObject($join, 'TransactionClocking');
        }

        return $this;
    }

    /**
     * Use the TransactionClocking relation TransactionClocking object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   TransactionClockingQuery A secondary query class using the current class as primary query
     */
    public function useTransactionClockingQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTransactionClocking($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TransactionClocking', 'TransactionClockingQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   Transaction $transaction Object to remove from the list of results
     *
     * @return TransactionQuery The current query, for fluid interface
     */
    public function prune($transaction = null)
    {
        if ($transaction) {
            $this->addUsingAlias(TransactionPeer::ID, $transaction->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
