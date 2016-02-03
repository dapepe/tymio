<?php


/**
 * Base class that represents a query for the 'holiday_domain' table.
 *
 *
 *
 * @method HolidayDomainQuery orderByHolidayId($order = Criteria::ASC) Order by the holiday_id column
 * @method HolidayDomainQuery orderByDomainId($order = Criteria::ASC) Order by the domain_id column
 *
 * @method HolidayDomainQuery groupByHolidayId() Group by the holiday_id column
 * @method HolidayDomainQuery groupByDomainId() Group by the domain_id column
 *
 * @method HolidayDomainQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method HolidayDomainQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method HolidayDomainQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method HolidayDomainQuery leftJoinDomain($relationAlias = null) Adds a LEFT JOIN clause to the query using the Domain relation
 * @method HolidayDomainQuery rightJoinDomain($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Domain relation
 * @method HolidayDomainQuery innerJoinDomain($relationAlias = null) Adds a INNER JOIN clause to the query using the Domain relation
 *
 * @method HolidayDomainQuery leftJoinHoliday($relationAlias = null) Adds a LEFT JOIN clause to the query using the Holiday relation
 * @method HolidayDomainQuery rightJoinHoliday($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Holiday relation
 * @method HolidayDomainQuery innerJoinHoliday($relationAlias = null) Adds a INNER JOIN clause to the query using the Holiday relation
 *
 * @method HolidayDomain findOne(PropelPDO $con = null) Return the first HolidayDomain matching the query
 * @method HolidayDomain findOneOrCreate(PropelPDO $con = null) Return the first HolidayDomain matching the query, or a new HolidayDomain object populated from the query conditions when no match is found
 *
 * @method HolidayDomain findOneByHolidayId(int $holiday_id) Return the first HolidayDomain filtered by the holiday_id column
 * @method HolidayDomain findOneByDomainId(int $domain_id) Return the first HolidayDomain filtered by the domain_id column
 *
 * @method array findByHolidayId(int $holiday_id) Return HolidayDomain objects filtered by the holiday_id column
 * @method array findByDomainId(int $domain_id) Return HolidayDomain objects filtered by the domain_id column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseHolidayDomainQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseHolidayDomainQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'HolidayDomain', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new HolidayDomainQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   HolidayDomainQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return HolidayDomainQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof HolidayDomainQuery) {
            return $criteria;
        }
        $query = new HolidayDomainQuery();
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
                         A Primary key composition: [$holiday_id, $domain_id]
     * @param     PropelPDO $con an optional connection object
     *
     * @return   HolidayDomain|HolidayDomain[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = HolidayDomainPeer::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1]))))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(HolidayDomainPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 HolidayDomain A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `holiday_id`, `domain_id` FROM `holiday_domain` WHERE `holiday_id` = :p0 AND `domain_id` = :p1';
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
            $obj = new HolidayDomain();
            $obj->hydrate($row);
            HolidayDomainPeer::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1])));
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
     * @return HolidayDomain|HolidayDomain[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|HolidayDomain[]|mixed the list of results, formatted by the current formatter
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
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(HolidayDomainPeer::HOLIDAY_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(HolidayDomainPeer::DOMAIN_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the holiday_id column
     *
     * Example usage:
     * <code>
     * $query->filterByHolidayId(1234); // WHERE holiday_id = 1234
     * $query->filterByHolidayId(array(12, 34)); // WHERE holiday_id IN (12, 34)
     * $query->filterByHolidayId(array('min' => 12)); // WHERE holiday_id >= 12
     * $query->filterByHolidayId(array('max' => 12)); // WHERE holiday_id <= 12
     * </code>
     *
     * @see       filterByHoliday()
     *
     * @param     mixed $holidayId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function filterByHolidayId($holidayId = null, $comparison = null)
    {
        if (is_array($holidayId)) {
            $useMinMax = false;
            if (isset($holidayId['min'])) {
                $this->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $holidayId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($holidayId['max'])) {
                $this->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $holidayId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $holidayId, $comparison);
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
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function filterByDomainId($domainId = null, $comparison = null)
    {
        if (is_array($domainId)) {
            $useMinMax = false;
            if (isset($domainId['min'])) {
                $this->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $domainId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($domainId['max'])) {
                $this->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $domainId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $domainId, $comparison);
    }

    /**
     * Filter the query by a related Domain object
     *
     * @param   Domain|PropelObjectCollection $domain The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 HolidayDomainQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByDomain($domain, $comparison = null)
    {
        if ($domain instanceof Domain) {
            return $this
                ->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $domain->getId(), $comparison);
        } elseif ($domain instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(HolidayDomainPeer::DOMAIN_ID, $domain->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return HolidayDomainQuery The current query, for fluid interface
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
     * Filter the query by a related Holiday object
     *
     * @param   Holiday|PropelObjectCollection $holiday The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 HolidayDomainQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByHoliday($holiday, $comparison = null)
    {
        if ($holiday instanceof Holiday) {
            return $this
                ->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $holiday->getId(), $comparison);
        } elseif ($holiday instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(HolidayDomainPeer::HOLIDAY_ID, $holiday->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByHoliday() only accepts arguments of type Holiday or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Holiday relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function joinHoliday($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Holiday');

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
            $this->addJoinObject($join, 'Holiday');
        }

        return $this;
    }

    /**
     * Use the Holiday relation Holiday object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   HolidayQuery A secondary query class using the current class as primary query
     */
    public function useHolidayQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinHoliday($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Holiday', 'HolidayQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   HolidayDomain $holidayDomain Object to remove from the list of results
     *
     * @return HolidayDomainQuery The current query, for fluid interface
     */
    public function prune($holidayDomain = null)
    {
        if ($holidayDomain) {
            $this->addCond('pruneCond0', $this->getAliasedColName(HolidayDomainPeer::HOLIDAY_ID), $holidayDomain->getHolidayId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(HolidayDomainPeer::DOMAIN_ID), $holidayDomain->getDomainId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

}
