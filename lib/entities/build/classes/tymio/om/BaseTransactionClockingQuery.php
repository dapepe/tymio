<?php


/**
 * Base class that represents a query for the 'transaction_clocking' table.
 *
 *
 *
 * @method TransactionClockingQuery orderByTransactionId($order = Criteria::ASC) Order by the transaction_id column
 * @method TransactionClockingQuery orderByClockingId($order = Criteria::ASC) Order by the clocking_id column
 *
 * @method TransactionClockingQuery groupByTransactionId() Group by the transaction_id column
 * @method TransactionClockingQuery groupByClockingId() Group by the clocking_id column
 *
 * @method TransactionClockingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method TransactionClockingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method TransactionClockingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method TransactionClockingQuery leftJoinClocking($relationAlias = null) Adds a LEFT JOIN clause to the query using the Clocking relation
 * @method TransactionClockingQuery rightJoinClocking($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Clocking relation
 * @method TransactionClockingQuery innerJoinClocking($relationAlias = null) Adds a INNER JOIN clause to the query using the Clocking relation
 *
 * @method TransactionClockingQuery leftJoinTransaction($relationAlias = null) Adds a LEFT JOIN clause to the query using the Transaction relation
 * @method TransactionClockingQuery rightJoinTransaction($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Transaction relation
 * @method TransactionClockingQuery innerJoinTransaction($relationAlias = null) Adds a INNER JOIN clause to the query using the Transaction relation
 *
 * @method TransactionClocking findOne(PropelPDO $con = null) Return the first TransactionClocking matching the query
 * @method TransactionClocking findOneOrCreate(PropelPDO $con = null) Return the first TransactionClocking matching the query, or a new TransactionClocking object populated from the query conditions when no match is found
 *
 * @method TransactionClocking findOneByTransactionId(int $transaction_id) Return the first TransactionClocking filtered by the transaction_id column
 * @method TransactionClocking findOneByClockingId(int $clocking_id) Return the first TransactionClocking filtered by the clocking_id column
 *
 * @method array findByTransactionId(int $transaction_id) Return TransactionClocking objects filtered by the transaction_id column
 * @method array findByClockingId(int $clocking_id) Return TransactionClocking objects filtered by the clocking_id column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseTransactionClockingQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseTransactionClockingQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'TransactionClocking', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new TransactionClockingQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   TransactionClockingQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return TransactionClockingQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof TransactionClockingQuery) {
            return $criteria;
        }
        $query = new TransactionClockingQuery();
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
                         A Primary key composition: [$transaction_id, $clocking_id]
     * @param     PropelPDO $con an optional connection object
     *
     * @return   TransactionClocking|TransactionClocking[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = TransactionClockingPeer::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1]))))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(TransactionClockingPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 TransactionClocking A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `transaction_id`, `clocking_id` FROM `transaction_clocking` WHERE `transaction_id` = :p0 AND `clocking_id` = :p1';
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
            $obj = new TransactionClocking();
            $obj->hydrate($row);
            TransactionClockingPeer::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1])));
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
     * @return TransactionClocking|TransactionClocking[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|TransactionClocking[]|mixed the list of results, formatted by the current formatter
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
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(TransactionClockingPeer::TRANSACTION_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(TransactionClockingPeer::CLOCKING_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the transaction_id column
     *
     * Example usage:
     * <code>
     * $query->filterByTransactionId(1234); // WHERE transaction_id = 1234
     * $query->filterByTransactionId(array(12, 34)); // WHERE transaction_id IN (12, 34)
     * $query->filterByTransactionId(array('min' => 12)); // WHERE transaction_id >= 12
     * $query->filterByTransactionId(array('max' => 12)); // WHERE transaction_id <= 12
     * </code>
     *
     * @see       filterByTransaction()
     *
     * @param     mixed $transactionId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function filterByTransactionId($transactionId = null, $comparison = null)
    {
        if (is_array($transactionId)) {
            $useMinMax = false;
            if (isset($transactionId['min'])) {
                $this->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $transactionId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($transactionId['max'])) {
                $this->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $transactionId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $transactionId, $comparison);
    }

    /**
     * Filter the query on the clocking_id column
     *
     * Example usage:
     * <code>
     * $query->filterByClockingId(1234); // WHERE clocking_id = 1234
     * $query->filterByClockingId(array(12, 34)); // WHERE clocking_id IN (12, 34)
     * $query->filterByClockingId(array('min' => 12)); // WHERE clocking_id >= 12
     * $query->filterByClockingId(array('max' => 12)); // WHERE clocking_id <= 12
     * </code>
     *
     * @see       filterByClocking()
     *
     * @param     mixed $clockingId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function filterByClockingId($clockingId = null, $comparison = null)
    {
        if (is_array($clockingId)) {
            $useMinMax = false;
            if (isset($clockingId['min'])) {
                $this->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $clockingId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($clockingId['max'])) {
                $this->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $clockingId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $clockingId, $comparison);
    }

    /**
     * Filter the query by a related Clocking object
     *
     * @param   Clocking|PropelObjectCollection $clocking The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByClocking($clocking, $comparison = null)
    {
        if ($clocking instanceof Clocking) {
            return $this
                ->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $clocking->getId(), $comparison);
        } elseif ($clocking instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TransactionClockingPeer::CLOCKING_ID, $clocking->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return TransactionClockingQuery The current query, for fluid interface
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
     * Filter the query by a related Transaction object
     *
     * @param   Transaction|PropelObjectCollection $transaction The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TransactionClockingQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTransaction($transaction, $comparison = null)
    {
        if ($transaction instanceof Transaction) {
            return $this
                ->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $transaction->getId(), $comparison);
        } elseif ($transaction instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TransactionClockingPeer::TRANSACTION_ID, $transaction->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByTransaction() only accepts arguments of type Transaction or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Transaction relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function joinTransaction($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Transaction');

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
            $this->addJoinObject($join, 'Transaction');
        }

        return $this;
    }

    /**
     * Use the Transaction relation Transaction object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   TransactionQuery A secondary query class using the current class as primary query
     */
    public function useTransactionQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTransaction($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Transaction', 'TransactionQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   TransactionClocking $transactionClocking Object to remove from the list of results
     *
     * @return TransactionClockingQuery The current query, for fluid interface
     */
    public function prune($transactionClocking = null)
    {
        if ($transactionClocking) {
            $this->addCond('pruneCond0', $this->getAliasedColName(TransactionClockingPeer::TRANSACTION_ID), $transactionClocking->getTransactionId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(TransactionClockingPeer::CLOCKING_ID), $transactionClocking->getClockingId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

}
