<?php


/**
 * Base class that represents a query for the 'clocking' table.
 *
 *
 *
 * @method ClockingQuery orderById($order = Criteria::ASC) Order by the id column
 * @method ClockingQuery orderByCreatorId($order = Criteria::ASC) Order by the creator_id column
 * @method ClockingQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method ClockingQuery orderByTypeId($order = Criteria::ASC) Order by the type_id column
 * @method ClockingQuery orderByStart($order = Criteria::ASC) Order by the start column
 * @method ClockingQuery orderByEnd($order = Criteria::ASC) Order by the end column
 * @method ClockingQuery orderByBreaktime($order = Criteria::ASC) Order by the breaktime column
 * @method ClockingQuery orderByComment($order = Criteria::ASC) Order by the comment column
 * @method ClockingQuery orderByApprovalStatus($order = Criteria::ASC) Order by the approval_status column
 * @method ClockingQuery orderByDeleted($order = Criteria::ASC) Order by the deleted column
 * @method ClockingQuery orderByFrozen($order = Criteria::ASC) Order by the frozen column
 * @method ClockingQuery orderByCreationdate($order = Criteria::ASC) Order by the creationdate column
 * @method ClockingQuery orderByLastChanged($order = Criteria::ASC) Order by the last_changed column
 *
 * @method ClockingQuery groupById() Group by the id column
 * @method ClockingQuery groupByCreatorId() Group by the creator_id column
 * @method ClockingQuery groupByUserId() Group by the user_id column
 * @method ClockingQuery groupByTypeId() Group by the type_id column
 * @method ClockingQuery groupByStart() Group by the start column
 * @method ClockingQuery groupByEnd() Group by the end column
 * @method ClockingQuery groupByBreaktime() Group by the breaktime column
 * @method ClockingQuery groupByComment() Group by the comment column
 * @method ClockingQuery groupByApprovalStatus() Group by the approval_status column
 * @method ClockingQuery groupByDeleted() Group by the deleted column
 * @method ClockingQuery groupByFrozen() Group by the frozen column
 * @method ClockingQuery groupByCreationdate() Group by the creationdate column
 * @method ClockingQuery groupByLastChanged() Group by the last_changed column
 *
 * @method ClockingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method ClockingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method ClockingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method ClockingQuery leftJoinUserRelatedByCreatorId($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRelatedByCreatorId relation
 * @method ClockingQuery rightJoinUserRelatedByCreatorId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRelatedByCreatorId relation
 * @method ClockingQuery innerJoinUserRelatedByCreatorId($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRelatedByCreatorId relation
 *
 * @method ClockingQuery leftJoinClockingType($relationAlias = null) Adds a LEFT JOIN clause to the query using the ClockingType relation
 * @method ClockingQuery rightJoinClockingType($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ClockingType relation
 * @method ClockingQuery innerJoinClockingType($relationAlias = null) Adds a INNER JOIN clause to the query using the ClockingType relation
 *
 * @method ClockingQuery leftJoinUserRelatedByUserId($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRelatedByUserId relation
 * @method ClockingQuery rightJoinUserRelatedByUserId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRelatedByUserId relation
 * @method ClockingQuery innerJoinUserRelatedByUserId($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRelatedByUserId relation
 *
 * @method ClockingQuery leftJoinTransactionClocking($relationAlias = null) Adds a LEFT JOIN clause to the query using the TransactionClocking relation
 * @method ClockingQuery rightJoinTransactionClocking($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TransactionClocking relation
 * @method ClockingQuery innerJoinTransactionClocking($relationAlias = null) Adds a INNER JOIN clause to the query using the TransactionClocking relation
 *
 * @method Clocking findOne(PropelPDO $con = null) Return the first Clocking matching the query
 * @method Clocking findOneOrCreate(PropelPDO $con = null) Return the first Clocking matching the query, or a new Clocking object populated from the query conditions when no match is found
 *
 * @method Clocking findOneByCreatorId(int $creator_id) Return the first Clocking filtered by the creator_id column
 * @method Clocking findOneByUserId(int $user_id) Return the first Clocking filtered by the user_id column
 * @method Clocking findOneByTypeId(int $type_id) Return the first Clocking filtered by the type_id column
 * @method Clocking findOneByStart(string $start) Return the first Clocking filtered by the start column
 * @method Clocking findOneByEnd(string $end) Return the first Clocking filtered by the end column
 * @method Clocking findOneByBreaktime(int $breaktime) Return the first Clocking filtered by the breaktime column
 * @method Clocking findOneByComment(string $comment) Return the first Clocking filtered by the comment column
 * @method Clocking findOneByApprovalStatus(int $approval_status) Return the first Clocking filtered by the approval_status column
 * @method Clocking findOneByDeleted(boolean $deleted) Return the first Clocking filtered by the deleted column
 * @method Clocking findOneByFrozen(boolean $frozen) Return the first Clocking filtered by the frozen column
 * @method Clocking findOneByCreationdate(int $creationdate) Return the first Clocking filtered by the creationdate column
 * @method Clocking findOneByLastChanged(int $last_changed) Return the first Clocking filtered by the last_changed column
 *
 * @method array findById(int $id) Return Clocking objects filtered by the id column
 * @method array findByCreatorId(int $creator_id) Return Clocking objects filtered by the creator_id column
 * @method array findByUserId(int $user_id) Return Clocking objects filtered by the user_id column
 * @method array findByTypeId(int $type_id) Return Clocking objects filtered by the type_id column
 * @method array findByStart(string $start) Return Clocking objects filtered by the start column
 * @method array findByEnd(string $end) Return Clocking objects filtered by the end column
 * @method array findByBreaktime(int $breaktime) Return Clocking objects filtered by the breaktime column
 * @method array findByComment(string $comment) Return Clocking objects filtered by the comment column
 * @method array findByApprovalStatus(int $approval_status) Return Clocking objects filtered by the approval_status column
 * @method array findByDeleted(boolean $deleted) Return Clocking objects filtered by the deleted column
 * @method array findByFrozen(boolean $frozen) Return Clocking objects filtered by the frozen column
 * @method array findByCreationdate(int $creationdate) Return Clocking objects filtered by the creationdate column
 * @method array findByLastChanged(int $last_changed) Return Clocking objects filtered by the last_changed column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseClockingQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseClockingQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'Clocking', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ClockingQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   ClockingQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return ClockingQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof ClockingQuery) {
            return $criteria;
        }
        $query = new ClockingQuery();
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
     * @return   Clocking|Clocking[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ClockingPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(ClockingPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 Clocking A model object, or null if the key is not found
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
     * @return                 Clocking A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `creator_id`, `user_id`, `type_id`, `start`, `end`, `breaktime`, `comment`, `approval_status`, `deleted`, `frozen`, `creationdate`, `last_changed` FROM `clocking` WHERE `id` = :p0';
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
            $obj = new Clocking();
            $obj->hydrate($row);
            ClockingPeer::addInstanceToPool($obj, (string) $key);
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
     * @return Clocking|Clocking[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|Clocking[]|mixed the list of results, formatted by the current formatter
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ClockingPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ClockingPeer::ID, $keys, Criteria::IN);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ClockingPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ClockingPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::ID, $id, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByCreatorId($creatorId = null, $comparison = null)
    {
        if (is_array($creatorId)) {
            $useMinMax = false;
            if (isset($creatorId['min'])) {
                $this->addUsingAlias(ClockingPeer::CREATOR_ID, $creatorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($creatorId['max'])) {
                $this->addUsingAlias(ClockingPeer::CREATOR_ID, $creatorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::CREATOR_ID, $creatorId, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(ClockingPeer::USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(ClockingPeer::USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the type_id column
     *
     * Example usage:
     * <code>
     * $query->filterByTypeId(1234); // WHERE type_id = 1234
     * $query->filterByTypeId(array(12, 34)); // WHERE type_id IN (12, 34)
     * $query->filterByTypeId(array('min' => 12)); // WHERE type_id >= 12
     * $query->filterByTypeId(array('max' => 12)); // WHERE type_id <= 12
     * </code>
     *
     * @see       filterByClockingType()
     *
     * @param     mixed $typeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByTypeId($typeId = null, $comparison = null)
    {
        if (is_array($typeId)) {
            $useMinMax = false;
            if (isset($typeId['min'])) {
                $this->addUsingAlias(ClockingPeer::TYPE_ID, $typeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($typeId['max'])) {
                $this->addUsingAlias(ClockingPeer::TYPE_ID, $typeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::TYPE_ID, $typeId, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByStart($start = null, $comparison = null)
    {
        if (is_array($start)) {
            $useMinMax = false;
            if (isset($start['min'])) {
                $this->addUsingAlias(ClockingPeer::START, $start['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($start['max'])) {
                $this->addUsingAlias(ClockingPeer::START, $start['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::START, $start, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByEnd($end = null, $comparison = null)
    {
        if (is_array($end)) {
            $useMinMax = false;
            if (isset($end['min'])) {
                $this->addUsingAlias(ClockingPeer::END, $end['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($end['max'])) {
                $this->addUsingAlias(ClockingPeer::END, $end['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::END, $end, $comparison);
    }

    /**
     * Filter the query on the breaktime column
     *
     * Example usage:
     * <code>
     * $query->filterByBreaktime(1234); // WHERE breaktime = 1234
     * $query->filterByBreaktime(array(12, 34)); // WHERE breaktime IN (12, 34)
     * $query->filterByBreaktime(array('min' => 12)); // WHERE breaktime >= 12
     * $query->filterByBreaktime(array('max' => 12)); // WHERE breaktime <= 12
     * </code>
     *
     * @param     mixed $breaktime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByBreaktime($breaktime = null, $comparison = null)
    {
        if (is_array($breaktime)) {
            $useMinMax = false;
            if (isset($breaktime['min'])) {
                $this->addUsingAlias(ClockingPeer::BREAKTIME, $breaktime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($breaktime['max'])) {
                $this->addUsingAlias(ClockingPeer::BREAKTIME, $breaktime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::BREAKTIME, $breaktime, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
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

        return $this->addUsingAlias(ClockingPeer::COMMENT, $comment, $comparison);
    }

    /**
     * Filter the query on the approval_status column
     *
     * Example usage:
     * <code>
     * $query->filterByApprovalStatus(1234); // WHERE approval_status = 1234
     * $query->filterByApprovalStatus(array(12, 34)); // WHERE approval_status IN (12, 34)
     * $query->filterByApprovalStatus(array('min' => 12)); // WHERE approval_status >= 12
     * $query->filterByApprovalStatus(array('max' => 12)); // WHERE approval_status <= 12
     * </code>
     *
     * @param     mixed $approvalStatus The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByApprovalStatus($approvalStatus = null, $comparison = null)
    {
        if (is_array($approvalStatus)) {
            $useMinMax = false;
            if (isset($approvalStatus['min'])) {
                $this->addUsingAlias(ClockingPeer::APPROVAL_STATUS, $approvalStatus['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($approvalStatus['max'])) {
                $this->addUsingAlias(ClockingPeer::APPROVAL_STATUS, $approvalStatus['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::APPROVAL_STATUS, $approvalStatus, $comparison);
    }

    /**
     * Filter the query on the deleted column
     *
     * Example usage:
     * <code>
     * $query->filterByDeleted(true); // WHERE deleted = true
     * $query->filterByDeleted('yes'); // WHERE deleted = true
     * </code>
     *
     * @param     boolean|string $deleted The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByDeleted($deleted = null, $comparison = null)
    {
        if (is_string($deleted)) {
            $deleted = in_array(strtolower($deleted), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ClockingPeer::DELETED, $deleted, $comparison);
    }

    /**
     * Filter the query on the frozen column
     *
     * Example usage:
     * <code>
     * $query->filterByFrozen(true); // WHERE frozen = true
     * $query->filterByFrozen('yes'); // WHERE frozen = true
     * </code>
     *
     * @param     boolean|string $frozen The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByFrozen($frozen = null, $comparison = null)
    {
        if (is_string($frozen)) {
            $frozen = in_array(strtolower($frozen), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(ClockingPeer::FROZEN, $frozen, $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByCreationdate($creationdate = null, $comparison = null)
    {
        if (is_array($creationdate)) {
            $useMinMax = false;
            if (isset($creationdate['min'])) {
                $this->addUsingAlias(ClockingPeer::CREATIONDATE, $creationdate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($creationdate['max'])) {
                $this->addUsingAlias(ClockingPeer::CREATIONDATE, $creationdate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::CREATIONDATE, $creationdate, $comparison);
    }

    /**
     * Filter the query on the last_changed column
     *
     * Example usage:
     * <code>
     * $query->filterByLastChanged(1234); // WHERE last_changed = 1234
     * $query->filterByLastChanged(array(12, 34)); // WHERE last_changed IN (12, 34)
     * $query->filterByLastChanged(array('min' => 12)); // WHERE last_changed >= 12
     * $query->filterByLastChanged(array('max' => 12)); // WHERE last_changed <= 12
     * </code>
     *
     * @param     mixed $lastChanged The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function filterByLastChanged($lastChanged = null, $comparison = null)
    {
        if (is_array($lastChanged)) {
            $useMinMax = false;
            if (isset($lastChanged['min'])) {
                $this->addUsingAlias(ClockingPeer::LAST_CHANGED, $lastChanged['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastChanged['max'])) {
                $this->addUsingAlias(ClockingPeer::LAST_CHANGED, $lastChanged['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ClockingPeer::LAST_CHANGED, $lastChanged, $comparison);
    }

    /**
     * Filter the query by a related User object
     *
     * @param   User|PropelObjectCollection $user The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByUserRelatedByCreatorId($user, $comparison = null)
    {
        if ($user instanceof User) {
            return $this
                ->addUsingAlias(ClockingPeer::CREATOR_ID, $user->getId(), $comparison);
        } elseif ($user instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ClockingPeer::CREATOR_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
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
     * Filter the query by a related ClockingType object
     *
     * @param   ClockingType|PropelObjectCollection $clockingType The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByClockingType($clockingType, $comparison = null)
    {
        if ($clockingType instanceof ClockingType) {
            return $this
                ->addUsingAlias(ClockingPeer::TYPE_ID, $clockingType->getId(), $comparison);
        } elseif ($clockingType instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ClockingPeer::TYPE_ID, $clockingType->toKeyValue('Id', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByClockingType() only accepts arguments of type ClockingType or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ClockingType relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function joinClockingType($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ClockingType');

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
            $this->addJoinObject($join, 'ClockingType');
        }

        return $this;
    }

    /**
     * Use the ClockingType relation ClockingType object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   ClockingTypeQuery A secondary query class using the current class as primary query
     */
    public function useClockingTypeQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinClockingType($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ClockingType', 'ClockingTypeQuery');
    }

    /**
     * Filter the query by a related User object
     *
     * @param   User|PropelObjectCollection $user The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByUserRelatedByUserId($user, $comparison = null)
    {
        if ($user instanceof User) {
            return $this
                ->addUsingAlias(ClockingPeer::USER_ID, $user->getId(), $comparison);
        } elseif ($user instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ClockingPeer::USER_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
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
     * Filter the query by a related TransactionClocking object
     *
     * @param   TransactionClocking|PropelObjectCollection $transactionClocking  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTransactionClocking($transactionClocking, $comparison = null)
    {
        if ($transactionClocking instanceof TransactionClocking) {
            return $this
                ->addUsingAlias(ClockingPeer::ID, $transactionClocking->getClockingId(), $comparison);
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
     * @return ClockingQuery The current query, for fluid interface
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
     * @param   Clocking $clocking Object to remove from the list of results
     *
     * @return ClockingQuery The current query, for fluid interface
     */
    public function prune($clocking = null)
    {
        if ($clocking) {
            $this->addUsingAlias(ClockingPeer::ID, $clocking->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
