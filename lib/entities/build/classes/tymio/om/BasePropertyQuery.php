<?php


/**
 * Base class that represents a query for the 'property' table.
 *
 *
 *
 * @method PropertyQuery orderById($order = Criteria::ASC) Order by the id column
 * @method PropertyQuery orderByAccountId($order = Criteria::ASC) Order by the account_id column
 * @method PropertyQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method PropertyQuery orderByLabel($order = Criteria::ASC) Order by the label column
 * @method PropertyQuery orderByDescription($order = Criteria::ASC) Order by the description column
 * @method PropertyQuery orderByType($order = Criteria::ASC) Order by the type column
 * @method PropertyQuery orderByDefaultValue($order = Criteria::ASC) Order by the default_value column
 * @method PropertyQuery orderByFixed($order = Criteria::ASC) Order by the fixed column
 *
 * @method PropertyQuery groupById() Group by the id column
 * @method PropertyQuery groupByAccountId() Group by the account_id column
 * @method PropertyQuery groupByName() Group by the name column
 * @method PropertyQuery groupByLabel() Group by the label column
 * @method PropertyQuery groupByDescription() Group by the description column
 * @method PropertyQuery groupByType() Group by the type column
 * @method PropertyQuery groupByDefaultValue() Group by the default_value column
 * @method PropertyQuery groupByFixed() Group by the fixed column
 *
 * @method PropertyQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method PropertyQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method PropertyQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method PropertyQuery leftJoinAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the Account relation
 * @method PropertyQuery rightJoinAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Account relation
 * @method PropertyQuery innerJoinAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the Account relation
 *
 * @method PropertyQuery leftJoinPropertyValue($relationAlias = null) Adds a LEFT JOIN clause to the query using the PropertyValue relation
 * @method PropertyQuery rightJoinPropertyValue($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PropertyValue relation
 * @method PropertyQuery innerJoinPropertyValue($relationAlias = null) Adds a INNER JOIN clause to the query using the PropertyValue relation
 *
 * @method Property findOne(PropelPDO $con = null) Return the first Property matching the query
 * @method Property findOneOrCreate(PropelPDO $con = null) Return the first Property matching the query, or a new Property object populated from the query conditions when no match is found
 *
 * @method Property findOneByAccountId(int $account_id) Return the first Property filtered by the account_id column
 * @method Property findOneByName(string $name) Return the first Property filtered by the name column
 * @method Property findOneByLabel(string $label) Return the first Property filtered by the label column
 * @method Property findOneByDescription(string $description) Return the first Property filtered by the description column
 * @method Property findOneByType(string $type) Return the first Property filtered by the type column
 * @method Property findOneByDefaultValue(string $default_value) Return the first Property filtered by the default_value column
 * @method Property findOneByFixed(boolean $fixed) Return the first Property filtered by the fixed column
 *
 * @method array findById(int $id) Return Property objects filtered by the id column
 * @method array findByAccountId(int $account_id) Return Property objects filtered by the account_id column
 * @method array findByName(string $name) Return Property objects filtered by the name column
 * @method array findByLabel(string $label) Return Property objects filtered by the label column
 * @method array findByDescription(string $description) Return Property objects filtered by the description column
 * @method array findByType(string $type) Return Property objects filtered by the type column
 * @method array findByDefaultValue(string $default_value) Return Property objects filtered by the default_value column
 * @method array findByFixed(boolean $fixed) Return Property objects filtered by the fixed column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BasePropertyQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BasePropertyQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'Property', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new PropertyQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   PropertyQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return PropertyQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof PropertyQuery) {
            return $criteria;
        }
        $query = new PropertyQuery();
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
     * @return   Property|Property[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PropertyPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(PropertyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 Property A model object, or null if the key is not found
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
     * @return                 Property A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `account_id`, `name`, `label`, `description`, `type`, `default_value`, `fixed` FROM `property` WHERE `id` = :p0';
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
            $obj = new Property();
            $obj->hydrate($row);
            PropertyPeer::addInstanceToPool($obj, (string) $key);
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
     * @return Property|Property[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|Property[]|mixed the list of results, formatted by the current formatter
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
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PropertyPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PropertyPeer::ID, $keys, Criteria::IN);
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
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PropertyPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PropertyPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PropertyPeer::ID, $id, $comparison);
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
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByAccountId($accountId = null, $comparison = null)
    {
        if (is_array($accountId)) {
            $useMinMax = false;
            if (isset($accountId['min'])) {
                $this->addUsingAlias(PropertyPeer::ACCOUNT_ID, $accountId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($accountId['max'])) {
                $this->addUsingAlias(PropertyPeer::ACCOUNT_ID, $accountId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PropertyPeer::ACCOUNT_ID, $accountId, $comparison);
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
     * @return PropertyQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PropertyPeer::NAME, $name, $comparison);
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
     * @return PropertyQuery The current query, for fluid interface
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

        return $this->addUsingAlias(PropertyPeer::LABEL, $label, $comparison);
    }

    /**
     * Filter the query on the description column
     *
     * Example usage:
     * <code>
     * $query->filterByDescription('fooValue');   // WHERE description = 'fooValue'
     * $query->filterByDescription('%fooValue%'); // WHERE description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $description The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByDescription($description = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($description)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $description)) {
                $description = str_replace('*', '%', $description);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PropertyPeer::DESCRIPTION, $description, $comparison);
    }

    /**
     * Filter the query on the type column
     *
     * Example usage:
     * <code>
     * $query->filterByType('fooValue');   // WHERE type = 'fooValue'
     * $query->filterByType('%fooValue%'); // WHERE type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $type The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByType($type = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($type)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $type)) {
                $type = str_replace('*', '%', $type);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PropertyPeer::TYPE, $type, $comparison);
    }

    /**
     * Filter the query on the default_value column
     *
     * Example usage:
     * <code>
     * $query->filterByDefaultValue('fooValue');   // WHERE default_value = 'fooValue'
     * $query->filterByDefaultValue('%fooValue%'); // WHERE default_value LIKE '%fooValue%'
     * </code>
     *
     * @param     string $defaultValue The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByDefaultValue($defaultValue = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($defaultValue)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $defaultValue)) {
                $defaultValue = str_replace('*', '%', $defaultValue);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PropertyPeer::DEFAULT_VALUE, $defaultValue, $comparison);
    }

    /**
     * Filter the query on the fixed column
     *
     * Example usage:
     * <code>
     * $query->filterByFixed(true); // WHERE fixed = true
     * $query->filterByFixed('yes'); // WHERE fixed = true
     * </code>
     *
     * @param     boolean|string $fixed The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function filterByFixed($fixed = null, $comparison = null)
    {
        if (is_string($fixed)) {
            $fixed = in_array(strtolower($fixed), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(PropertyPeer::FIXED, $fixed, $comparison);
    }

    /**
     * Filter the query by a related Account object
     *
     * @param   Account|PropelObjectCollection $account The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 PropertyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAccount($account, $comparison = null)
    {
        if ($account instanceof Account) {
            return $this
                ->addUsingAlias(PropertyPeer::ACCOUNT_ID, $account->getId(), $comparison);
        } elseif ($account instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PropertyPeer::ACCOUNT_ID, $account->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return PropertyQuery The current query, for fluid interface
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
     * Filter the query by a related PropertyValue object
     *
     * @param   PropertyValue|PropelObjectCollection $propertyValue  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 PropertyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByPropertyValue($propertyValue, $comparison = null)
    {
        if ($propertyValue instanceof PropertyValue) {
            return $this
                ->addUsingAlias(PropertyPeer::ID, $propertyValue->getPropertyId(), $comparison);
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
     * @return PropertyQuery The current query, for fluid interface
     */
    public function joinPropertyValue($relationAlias = null, $joinType = Criteria::INNER_JOIN)
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
    public function usePropertyValueQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPropertyValue($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PropertyValue', 'PropertyValueQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   Property $property Object to remove from the list of results
     *
     * @return PropertyQuery The current query, for fluid interface
     */
    public function prune($property = null)
    {
        if ($property) {
            $this->addUsingAlias(PropertyPeer::ID, $property->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
